<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends DefaultApi{
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		// $this->load->library('session');
	}
	
	public function app_config(){
		/**
		 *	Code for update app_config
		 */
		$this->Expired = 1;

		if(!($app_config=$this->Getfile($this->config->item('app_config_path')))||isset($_GET['gen'])){

			$app_config = $this->config->item("app_config");
			$app_config = json_encode(array(
				'data'=>$app_config,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);

			$this->Savefile($this->config->item('app_config_path'),$app_config);
		}

		$this->PushData($app_config);
	}
	
	public function hot_search(){
		
		$json = json_decode( file_get_contents($this->config->item('hot_search_path')),true);
		foreach($json as $k => $v){
			$json[$k] = ncr2str($v);
		}
		$json['result'] = 1;
		header("Content-type:application/json");

		echo json_encode($json,JSON_UNESCAPED_SLASHES);
	}
	
	//For daily & instant only
	public function hit_list($section){
		$json = array('data'=>array());
		$json['result'] = 1;
		$error = false;
		if($section == 1){
			$file = $this->config->item('instant_top_list_path');
		}
		else if($section == 2){
			$file = $this->config->item('daily_top_list_path');
		}
		else{
			$error = true;
		}
		if(!$error){
			$this->load->model('Section');
			$cat_list = $this->Section->Get_cat_list($section);
			$map_cat = array_combine (array_column($cat_list,'mapping_catid'), array_column($cat_list,'cat_id'));
			
			$tmp = json_decode(file_get_contents($file),true);
			foreach($tmp as $name => $list){
				if($name != 'day'){
					continue;
				}
				foreach($list as $k=>$v){
					//top 10 only
					if($k>9){
						break;
					}
					$imgs = array();
					$video = "";
					$writer = array();	
					if(isset($v['columnistID'])&&$v['catID']==9){
						// $writer = array('name'=>'test');
					}
					
					$json['data'][$k] = array(
						'id' => $v['newsId'],
						'title' => $v['title'],
						'content' => "",
						'section' => $v['catID']==9?'5':$section,
						'cat'	=> $v['catID']==9?'1':$map_cat[$v['catID']],
						'publish_datetime'=>$v['publishDatetime'],
						'imgs'=>$imgs,
						'vdo'=>$video,
						'writer'=>$writer,//專欄顯示
						'layout'=>"",//日報為空
					);
					
				}
			}
			$json['data'] = $this->list_cast($json['data']);
		}else{
			$json['result'] = 0;
		}
		
		header("Content-type:application/json");
		echo json_encode($json);
			
	}
	
	public function interest(){
		
	}
	
	public function detail($section, $id){
		
	}
	
	public function list($section=2, $cat=1,$page=1){

		$error = true;

		if($section!=2||$cat==''){
			$error = false;
		}else{
			$this->load->model('Section');
			$num = $this->Section->Check_cat_list(2,$cat);
			$error = ($num!=0);
		}

		if($error){
			$this->Expired = 1;

			$path = str_replace('{cat}',$cat,$this->config->item('daily_list_path'));

			if((int)$page>1){
				$path = str_replace('.json','_'.(int)$page.'json',$path);
			}else{
				$page = 1;
			}
			if(!($daily_list=$this->Getfile($path))||isset($_GET['gen'])){
				
				$this->load->model('Cat');
				$data = $this->Cat->Get_cat_list($section,$cat,(int)$page);
				if($data){
					$data['result'] = 1;
					$data['data'] = $this->list_cast($data['data']);
					$daily_list = json_encode($data,JSON_UNESCAPED_SLASHES);
				}
				
				
				
				$this->Savefile($path,$daily_list);
			}
		}else{
			$daily_list = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}
		
		
		$this->PushData($daily_list);
	}
	
	public function column_list($columnid){
		
	}
	
	public function section(){

		$this->Expired = 1;

		if(!($section_list=$this->Getfile($this->config->item('section_list_path')))||isset($_GET['gen'])){

			$this->load->model('Section');
			$section_list = $this->Section->Get_Section_list();
			$section_list = json_encode(array(
				'data'=>$section_list,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);

			$this->Savefile($this->config->item('section_list_path'),$section_list);
		}

		$this->PushData($section_list);
	}
	
	private function list_cast($data){
		$return_data = array();
		$list = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> "",
			"section"				=> "",
			"cat"					=> "",
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			"writer"				=> array(),
			"layout"				=> "",
		);
		
		foreach($data as $i => $d){
			$tmp = $list;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
			}
			$return_data[$i] = $tmp;
		}
		return $return_data;
	}
	
	private function detail_cast($data){
		$return_data = array();
		$detail = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> array(),
			"section"				=> "",
			"cat"					=> "",
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			"writer"				=> array(),
			"layout"				=> "",
			"keyword"				=> array(),
			"topic"					=> array(),
		);
		
		foreach($data as $i => $d){
			$tmp = $detail;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
			}
			$return_data[$i] = $tmp;
		}
		return $return_data;
	}
}