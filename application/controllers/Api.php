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
				'result' => 0
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
		echo json_encode($json,JSON_UNESCAPED_SLASHES);
	}
	
	public function detail($section, $id){
		
		
		
		
	}
	
	public function list($section, $cat){
		$this->Expired = 1;

		$path = str_replace('{cat}',$cat,$this->config->item('daily_list_path'));

		if(!($daily_list=$this->Getfile($path))||isset($_GET['gen'])){

			// $this->load->model('Section');
			// $section_list = $this->Section->Get_Section_list();
			//根据section确定返回数据
			$daily_list = json_encode(array(
				'data'=>$daily_list,
				'result' => 0
			),JSON_UNESCAPED_SLASHES);

			// $this->Savefile($this->config->item('section_list_path'),$section_list);
		}

		$this->PushData($section_list);
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
				'result' => 0
			),JSON_UNESCAPED_SLASHES);

			$this->Savefile($this->config->item('section_list_path'),$section_list);
		}

		$this->PushData($section_list);
	}
	
	
	

	
	
}