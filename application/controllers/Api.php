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
	
	//For daily & instant only
	public function hit_list($section){
		$output = array('data'=>array());
		$output['result'] = 1;
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
			$SectionName = $this->Section->Get_Section($section)[0]->section_name;
			$map_cat = array_combine (array_column($cat_list,'mapping_catid'), array_column($cat_list,'cat_id'));
			
			$tmp = json_decode(file_get_contents($file),true);
			foreach($tmp as $name => $list){
				if($name != 'day'){
					continue;
				}
				foreach($list as $k=>$v){
					//top 10 only
					$is_column = $v['catID']==9 && $section == 2;
					if($k>9){
						break;
					}
					
					$video = isset($v['video_path_1'])&&!empty($v['video_path_1'])?$v['video_path_1']:"";
					$writer = array();	
					if(isset($v['columnistID'])&&$is_column){
						// $writer = array('name'=>'test');
					}
					
					$output['data'][]= array(
						'id' => $v['newsId'],
						'title' => $v['title'],
						'section' => $is_column?'5':$section,
						'cat'	=> $is_column?'1':$map_cat[$v['catID']],
						'publish_datetime'=>$v['publishDatetime'],
						'vdo'=>$video,
						'writer'=>$writer,//專欄顯示
						'layout'=>"",//日報為空
					);
					$this->load->model($SectionName);
					// var_dump($section);
					$this->$SectionName->SetImg($output['data'],array());
					
					
				}
			}
			$output['data'] = $this->list_cast($output['data']);
		}
		else{
			$output['result'] = 0;
		}
		
		header("Content-type:application/json");
		echo json_encode($output);
			
	}

	public function interest($id = -1){
		
	}

	public function detail($section, $id){
		
		$this->load->model('Section');
		$res = $this->Section->Get_Section($section);
		$error = true;
		$error = (count($res)>0);

		if($error){

			$section_name = $res[0]->section_name;
			$this->load->model($section_name);
			$this->$section_name->SetSectionId($section)->SetId($id);

			$this->Expired = $this->$section_name->Expired;
			
			$path = $this->$section_name->GetPath($section_name);
			// if(!($detail=$this->Getfile($path))||isset($_GET['gen'])){
				$data = $this->$section_name->GetData();
				if($data){
					$data['result'] = 1;
					$detail = json_encode($data,JSON_UNESCAPED_SLASHES);
					$this->Savefile($path,$detail);
				}else{
					$detail = json_encode(array(
						'result' =>0
					),JSON_UNESCAPED_SLASHES);
				}

			// }
		}else{
			$detail = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}

		$this->PushData($detail);
	}
	
	public function list($section=2, $cat=1,$page=1){

		$error = true;
		$this->load->model('Section');

		if($section!=2||$cat==''){
			$error = false;
		}else{
			
			$num = $this->Section->Check_cat_list(2,$cat);
			$error = ($num!=0);
		}

		if($error){

			$SectionName = $this->Section->Get_Section($section)[0]->section_name;

			$this->load->model($SectionName);
			// var_dump($section);
			$this->$SectionName->SetSectionId($section)->SetCatId($cat)->page($page);
			$this->Expired = $this->$SectionName->Expired;

			if(!($output=$this->Getfile($this->$SectionName->path))||isset($_GET['gen'])){
				
				$data = $this->$SectionName->GetListData();
				if($data){
					$data['result'] = 1;
					$data['data'] = $this->list_cast($data['data']);
					$output = json_encode($data,JSON_UNESCAPED_SLASHES);
				}
				$this->Savefile($this->$SectionName->path,$output);
			}

		}else{
			$output = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}

		$this->PushData($output);
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


	public function demo($section=2,$id){

		$this->load->model('Section');
		$res = $this->Section->Get_Section($section);
		$error = true;
		$error = (count($res)>0);

		if($error){

			$this->load->model('Detail');
			$this->Detail->SetSection($section)->SetId($id);
			$path = $this->Detail->GetPath($res[0]->section_name);
			//if(!($detail=$this->Getfile($path))||isset($_GET['gen'])){
				$data = $this->Detail->GetData();
				if($data){
					$data['result'] = 1;
					$detail = json_encode($data,JSON_UNESCAPED_SLASHES);
					// $this->Savefile($path,$detail);
				}else{
					$detail = json_encode(array(
						'result' =>0
					),JSON_UNESCAPED_SLASHES);
				}

			//}
		}else{
			$detail = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}

		$this->PushData($detail);

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
			$return_data[] = $tmp;
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
			"related_news"			=> array(),
		);
		
		foreach($data as $i => $d){
			$tmp = $detail;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
			}
			$return_data[] = $tmp;
		}
		return $return_data;
	}
	
	

	
	
}