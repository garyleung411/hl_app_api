<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller{
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		// $this->load->library('session');
	}
	
	public function app_config(){
		/**
		 *	Code for update app_config
		 */
		if(isset($_GET['gen'])){
			
			$app_config = $this->config->item("app_config");
			$app_config = json_encode($app_config);
			file_put_contents($this->config->item('app_config_path'),$app_config);
			//echo '<pre>';
			//var_dump($app_config);
		}
		header("Content-type:application/json");
		echo file_get_contents($this->config->item('app_config_path'));
		
	}
	
	public function hot_search(){
		
		$json = json_decode( file_get_contents($this->config->item('hot_search_path')),true);
		foreach($json as $k => $v){
			$json[$k] = ncr2str($v);
		}
		header("Content-type:application/json");
		echo json_encode($json);
	}
	
	//For daily & instant only
	public function hit_list($section){
		
		if($section == 1){
			$file = $this->config->item('instant_top_list_path');
		}
		else if($section == 2){
			$file = $this->config->item('daily_top_list_path');
		}
		$json = array();
		$tmp = json_decode(file_get_contents($file),true);
		foreach($tmp as $k => $v){
			$
			
		}
		
		
		
		
		header("Content-type:application/json");
		echo json_encode($json);
	}
	
	
	
	public function detail($section, $id){
		
		
		
		
	}
	
	public function list($section, $cat){
		
	}
	
	public function column_list($columnid){
		
	}
	
	public function section($section){
		
	}
	

}