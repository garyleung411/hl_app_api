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
		echo json_encode($json,JSON_UNESCAPED_SLASHES);
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
	
	
	

	
	
}