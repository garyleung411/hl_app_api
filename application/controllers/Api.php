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
			$app_config = json_encode($app_config,JSON_UNESCAPED_SLASHES);
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
		echo json_encode($json,JSON_UNESCAPED_SLASHES);
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