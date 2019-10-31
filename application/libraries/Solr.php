<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define("SOLR_CONFIG_ERROR", "Error config info!");
define("SOLR_ERROR_NO_PROJECT", "No such project found!");
define("SOLR_ERROR_NO_SEARCH_KEY", "Please input a keywords for search");

class Solr
{
	public static $ALLOWED_PRODUCT=array(
		"HL"
	);
	protected $project;
	protected $httpRoot;
	protected $debug = false;
	protected $isCloudMode;
	protected $minScore = 0.1;
	
	public function __construct($config){
		$this->project = (isset($config['project']) && !empty($config['project']))?$config['project']:$this->error(SOLR_ERROR_NO_PROJECT);
		$this->httpRoot = isset($config['http_root'])?$config['http_root']:$this->error(SOLR_CONFIG_ERROR);
		$this->debug = isset($config['debug'])?$config['debug']:false;
		$this->isCloudMode = isset($config['is_cloud_mode'])?$config['is_cloud_mode']:$this->error(SOLR_CONFIG_ERROR);
		$this->minScore = isset($config['min_score'])?$config['min_score']:0.1;
	}
	public function update($arr){
		$url = $this->httpRoot.$this->project."/update?wt=json";
		return Curl::postJson($url, $arr);
	}
	public function delete($query){
		$url = $this->httpRoot.$this->project."/update";
		$str = "stream.body=<delete><query>".urlencode($query)."</query></delete>&commit=true";
		return Curl::postJson($url, $str, true);
	}
	public function search($query){
		if(is_array($query)){
			if(isset($query['keyword']) && !empty($query['keyword'])){
				if(isset($query['fields']) && !empty($query['fields'])){
					$conditionArr = array();
					foreach($query['fields'] as $field){
						$conditionArr[] = urlencode("(".$field.":".$query['keyword'].")");
					}
					$q = "q=".join("+OR+",$conditionArr);
				}else{
					$q = "q=".urlencode($query['keyword']);
				}
				if(isset($query['start'])){
					$q.= "&start=".$query['start'];
				}
				if(isset($query['rows'])){
					$q.= "&rows=".$query['rows'];
				}
				if(isset($query['sort']) && !empty($query['sort'])){
					$condition = array();
					foreach($query['sort'] as $sortField => $order){
						$condition[] = $sortField."+".$order;
					}
					if(!empty($condition)){
						$q.= "&sort=".join("%2C+",$condition);
					}
				}
			}else{
				$this->error(SOLR_ERROR_NO_SEARCH_KEY);
				return false;
			}
		}else{
			$q = "q=".urlencode($query);
			if(empty($q)){
				$this->error(SOLR_ERROR_NO_SEARCH_KEY);
				return false;
			}
		}
		$str = $q."&wt=json&indent=true&fq=%7B!frange+l%3D".$this->minScore."%7Dquery(%24q)";
		$url = $this->httpRoot.$this->project."/select";
		return Curl::postJson($url, $str, true);
	}
	public function optimize(){
		$url = $this->httpRoot.$this->project."/update?optimize=true";
		return Curl::postJson($url, "", true);
	}
	public function reload(){
		if($this->isCloudMode){
			//Cloud Method
			$url = $this->httpRoot."admin/collections?action=RELOAD&name=".$this->project;
		}else{
			//Single Core Method
			$url = $this->httpRoot."admin/cores?action=RELOAD&core=".$this->project;
		}
		return Curl::postJson($url, "", true);
	}
	public function error($msg){
		if($this->debug)
			echo "$msg<br>";
	}
	public function setDebug($isDebug){
		$this->debug = $isDebug;
	}
}
class Curl
{
	public static function postJson($url, $value, $search = false){
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_USERAGENT,'php:curl');		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		if(!empty($value)){
			if($search){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $value);
			}else{
				$str = json_encode($value);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Content-Length: ' . strlen($str)));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
			}
		}
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$results = curl_exec($ch);
		curl_close($ch);
		return (json_decode($results, true) === null)?$results:json_decode($results, true);
	}
}
?>