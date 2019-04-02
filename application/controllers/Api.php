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
	//十大
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
			// var_dump($file);
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

	//感興趣
	public function interest(){

		$this->Expired = 1;
		$path = $this->config->item('interest_list_path');
		$fileid = rand(0,9);
		$outputpath = str_replace('{page}',$fileid,$path);

		if(!($output=$this->Getfile($outputpath))||isset($_GET['gen'])){

			$this->load->model('Instant');

			$this->Instant->SetSectionId(1);
			
			$data = $this->Instant->GetInterestList();
			$file = array();
			$fileidlist = array();

			foreach ($data as $key => $value) {
				$file[(($key+1)%10)][] = $value;
				$fileidlist[] = (($key+1)%10);
			}
			foreach ($file as $k => $v) {
				$filepath = str_replace('{page}',$k,$path);
				$filedata['result'] = 1;
				$filedata['data'] = $this->list_cast($file[$k]);
				$data = json_encode($filedata,true);
				$this->Savefile($filepath,$data);

				if(in_array($k,$fileidlist)&&$k==$fileid){
					$output = $data;
				}
			}
			if($output==false||$output==''){
				$output = json_encode(array(
						'result' =>0
					),JSON_UNESCAPED_SLASHES);
			}
		}
		$this->PushData($output);
	}

	public function detail($section, $id){
		
		$this->load->model('Section');
		$res = $this->Section->Get_Section($section);
		$error = true;
		$error = (count($res)>0);

		if($error){

			$section_name = $res[0]->section_name;
			$this->load->model($section_name);
			$this->$section_name->SetSectionId($section);
			$this->Expired = $this->$section_name->Expired;
			
			
			$path= $this->config->item('detail_path');
			$path = str_replace('{section}',$section_name,$path);
			$page = ((int)($id/1000)+1)*1000;
			$path = str_replace('{page}',$page,$path);
			$path = str_replace('{id}',$id,$path);
			
			
			if(!($output=$this->Getfile($path))||isset($_GET['gen'])){

				$data = $this->$section_name->GetDetail($id);
				if($data){
					$data['result'] = 1;
					$data['data'] = $this->detail_cast($data['data']);
					$output = json_encode($data,JSON_UNESCAPED_SLASHES);
					$this->Savefile($path,$output);
					
				}else{
					$output = json_encode(array(
						'result' =>0
					),JSON_UNESCAPED_SLASHES);
				}

			}
		}
		else{
			$output = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}

		$this->PushData($output);
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
			$path = str_replace('{section}',$SectionName,$this->config->item('list_path'));
			$path = str_replace('{cat}',$cat,$path);
			$path = str_replace('.json','_'.(int)$page.'json',$path);
			
			
			if(!($output=$this->Getfile($path))||isset($_GET['gen'])){
				
				$data = $this->$SectionName->GetList();
				if($data){
					$data['result'] = 1;
					$data['data'] = $this->list_cast($data['data']);
					$output = json_encode($data,JSON_UNESCAPED_SLASHES);
				}
				// var_dump($path);
				$this->Savefile($path,$output);
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
		
		foreach ($detail as $i => $d) {
			if($i=='content'){
				$return_data[$i] = array(
					$data['content'],
					$data['content2'],
					$data['content3'],

				);
				continue;
			}
			if($i=='keyword'){
				$keyword = explode(';',$data['keyword']);
				if($keyword[0]==''){
					unset($keyword[0]);
				}
				$data['keyword'] = $keyword;
			}
			$return_data[$i] = isset($data[$i])?$data[$i]:$d;
 		}
		if(count($return_data["related_news"])>0){
			$return_data["related_news"] = $this->list_cast($return_data["related_news"]);
		}
		return $return_data;
	}
	public function demo()
	{
		$this->load->model('Instant');
		$this->Instant->SetSectionId(1)->Get_All_News_list('a',50,0,false);
	}
	
	// private function interest_cast($data){
	// 	$return_data = array();
	// 	$list = array(
	// 		"id"					=> "",
	// 		"title"					=> "",
	// 		"content"				=> "",
	// 		"section"				=> "",
	// 		"cat"					=> "",
	// 		"publish_datetime"		=> "",
	// 		"vdo"					=> "",
	// 		"imgs"					=> array(),
	// 		"writer"				=> array(),
	// 		"layout"				=> "",
	// 	);
		
	// 	foreach($data as $i => $d){
	// 		$tmp = $list;
	// 		foreach($tmp as $k => $v){
	// 			$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
	// 		}
	// 		$return_data[] = $tmp;
	// 	}
	// 	return $return_data;
	// }

	
	
}