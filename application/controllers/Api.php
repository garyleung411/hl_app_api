<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends DefaultApi{
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		// $this->load->library('session');
	}
	
	private function index_section(){
		$other = array(
			"SectionID" => "other",
			"name" => "期它類別",
			"SectionName" => "other",
			"CatList"	=> array(),
		);
		$topic =array();
		$tmp = json_decode($this->topic(true),true);
		
		if($tmp['result'] == 1){
			$catlist = array();
			foreach($tmp['data'] as $v){
				$cat = array(
					"CatID" => $v['id'],
					"CatName" => $v['title'],
					"MappingCatID" => "",
				);
				$catlist[] = $cat;
			}
			$topic = array(
				"SectionID" => "topic",
				"name" => "話題新聞",
				"SectionName" => "topic",
				"CatList"	=> $catlist,
			);
			
		}
		return array($other, $topic);
		
	}
	
	public function app_config(){
		/**
		 *	Code for update app_config
		 */
		$this->Expired = 1;

		if(!($data=json_decode($this->Getfile($this->config->item('app_config_path')),true))||isset($_GET['gen'])){
			$data = $this->config->item("app_config");
			$app_config = json_encode(array(
				'data'=>$data,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);
			$this->Savefile($this->config->item('app_config_path'),$app_config);
		}
		$data['special'] = array();
		$tmp = json_decode($this->special(true), true);
		if($tmp['result']==1){
			$data['special'] = $tmp['data'];
		}
		
		$app_config = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($app_config);
	}
	
	public function special($is_return = false){
		$this->load->model('Special');
		$special = $this->Special->get_special();
		$empty = false;
		if(count($special)>0){
			$special = $special[0];
		}		
		else{
			$empty = true;
		}
		
		if($empty){
			$output = json_encode(array(
				'result' =>0,
			),JSON_UNESCAPED_SLASHES);
		}
		else{
			$output = json_encode(array(
				'result' =>1,
				'data' => $special,
			),JSON_UNESCAPED_SLASHES);
		}
		
		if($is_return){
			return $output;
		}
		else{
			$this->PushData($output);
		}
	}
	
	public function topic($is_return = false){
		$this->load->model('Topic');
		$all_topic = $this->Topic->get_all_topic();
		$empty = false;
		if(count($all_topic)==0){
			$empty = true;
		}
		if($empty){
			$output = json_encode(array(
				'result' =>0,
			),JSON_UNESCAPED_SLASHES);
		}
		else{
			$output = json_encode(array(
				'result' =>1,
				'data' => $all_topic,
			),JSON_UNESCAPED_SLASHES);
		}
		
		if($is_return){
			return $output;
		}
		else{
			$this->PushData($output);
		}
	}
	
	public function hot_search(){
		
		$json = json_decode( file_get_contents($this->config->item('hot_search_path')),true);
		foreach($json as $k => $v){
			$json[$k] = ncr2str($v);
			
		}
		$output = array('data'=>$json);
		$output['result'] = 1;
		$output = json_encode($output,JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
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
					if($section == 1){
						$v['newsId'] = $v['newsId'] - 500000;
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
					// var_dump($output['data']);exit;
					$this->$SectionName->SetImg($output['data'],array());
					
					
				}
			}
			$output['data'] = $this->list_cast($output['data']);
		}
		else{
			$output['result'] = 0;
		}
		$output = json_encode($output,JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
			
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

	public function detail($section, $id, $cat = null){
		// if($section == "other" && $cat != null){
			// switch($cat){
				
			// }
		// }
		
		if($section == "topic"){
			$this->detail("1", $id);
			return;
		}
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
				// var_dump($data);
				if($data){
					
					$data = $this->detail_cast($data['data']);
					$output = json_encode(array(
						'data'=>$data,
						'result' => 1
					),JSON_UNESCAPED_SLASHES);
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
		
		$data = json_decode($output,true);
		if(isset($data['data'])){
			$data = $data['data'];
			if($data["section"]==1){
				$this->load->model("Topic");
				$data["topic"] = $this->Topic->is_topic_keyword($data["keyword"]);
			}
			
			$output = json_encode(array(
				'data'=>$data,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);
		}
		
		
		$this->PushData($output);
	}
	
	public function list($section, $cat, $page =1){
		if($section == "topic"){
			$this->topic_list($cat);
			return;
		}
		
		
		$error = true;
		$this->load->model('Section');
		
		if($cat==''){
			$error = false;
		}else{
			
			$num = $this->Section->Check_cat_list($section,$cat);
			$error = ($num!=0);
		}

		if($error){

			$SectionName = $this->Section->Get_Section($section)[0]->section_name;

			$this->load->model($SectionName);
			// var_dump($section);
			// var_dump($cat);
			$this->$SectionName->SetSectionId($section)->SetCatId($cat)->page($page);
			
			$this->Expired = $this->$SectionName->Expired;
			$path = str_replace('{section}',$SectionName,$this->config->item('list_path'));
			$path = str_replace('{cat}',$cat,$path);
			$path = str_replace('.json','_'.(int)$page.'json',$path);
			
			
			if(!($output=$this->Getfile($path))||isset($_GET['gen'])){
				
				$data = $this->$SectionName->GetList();
				if($data){
					
					$data = $this->list_cast($data['data']);
					
					$output = json_encode(array(
						'data'=>$data,
						'result' => 1
					),JSON_UNESCAPED_SLASHES);
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
	
	private function topic_list($cat){
		$empty = false;
		$this->load->model('Topic');
		$list = array();
		if($cat > $this->config->item('total_topic')){
			$empty = true;
		}
		else{
			$topic = $this->Topic->get_all_topic();
			
			if(in_array($cat,array_column($topic,'id'))){
				$i = array_search($cat,array_column($topic,'id'));
				$keyword = array_column($topic,'keyword')[$i];
				$this->load->model('Instant');
				$list = $this->Instant->get_list_by_keyword($keyword);
				if(count($list)<1){
					$empty = true;
				}
			}
			else{
				$empty = true;
			}
		}
		$list = $this->list_cast($list);
		if($empty){
			$output = json_encode(array(
				'result' =>0
			),JSON_UNESCAPED_SLASHES);
		}else{
			foreach($list as $k => $v){
				$v['section'] = 'topic';
				$v['cat'] = $cat;
				$list[$k] = $v;
			}
			$output = json_encode(array(
				'data' => $list,
				'result' =>1,
			),JSON_UNESCAPED_SLASHES);
		}
		$this->PushData($output);
			
		
	}
	
	public function column_list($columnid){
		
	}
	
	public function section(){

		$this->Expired = 100;

		if(!($data=json_decode($this->Getfile($this->config->item('section_list_path')),true))||isset($_GET['gen'])){

			$this->load->model('Section');
			$data = $this->Section->Get_Section_list();
			$section_list = json_encode(array(
				'data'=>$data,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);

			$this->Savefile($this->config->item('section_list_path'),$section_list);
		}
		else{
			$data = $data['data'];
		}
		$extra = $this->index_section();
		foreach($extra as $v){
			$data[] = $v;
		}
		
		$section_list = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
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
			"related_news"			=> array(),
			"topic"					=> array(),
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
	
	public function highlight(){

		$this->load->model('Highlight');
		$data = $this->Highlight->Get_highlight_list();
		// var_dump($data);
		$data[50] = array ( 
			'id' => '81665', 
			'title' => '《鐵探》演出獲激讚              姜皓文直認恨攞視帝', 
			'section' => "3", 
			'cat' => '3', 
			'publish_datetime' => '2019-04-11 23:00:00', 
			'vdo' => 'hkheadline/instant_video/2019/0411/186c944aaab36304e61635dfacc5e488.mp4', 
			'imgs' => array ( 0 => array ( 'path' => '/2019/04/11/Img_81665_500_190411153139.jpg', 'isCover' => '1', ), ), 
		);
		$data = $this->list_cast($data);
		$highlight_list = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		
		$this->PushData($highlight_list);
	}
	
	public function show_error($error_code = 0){
		$output = json_encode(array(
				'result' =>$error_code,
			),JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
		exit;
	}
	
	public function demo(){
		// var_dump($cat
		// $this->load->model('Instant');
		// $this->Instant->SetSectionId(1)->Get_All_News_list('a',50,0,false);
	}
	
	public function test($section, $cat, $page =1){
		if($section == "topic"){
			$this->topic_list($cat);
			return;
		}
		$this->load->model('News_category_list');
		$is_cat = $this->News_category_list->Check_Cat($section,$cat);
		if($is_cat){
			$map_cat = $this->News_category_list-> Mapping($section,$cat);
			$this->load->model('Section');
			$section_name = $this->Section->Get_Section($section)[0]->section_name;
			$this->load->model($section_name);
			$this->$section_name->page($page);
			$this->Expired = $this->config->item("list_time");
			$path = str_replace('{section}',$section_name,$this->config->item('list_path'));
			$path = str_replace('{cat}',$cat,$path);
			$path = str_replace('.json','_'.(int)$page.'json',$path);
			if(!($output=$this->Getfile($path))||isset($_GET['gen'])){
				$list = $this->$section_name->GetList($map_cat);
				if($list){
					foreach($list as $k=>$v){
						// var_dump($list);exit;
						$v["section"] = $section;
						$v["cat"] = $cat;
						$list[$k] = $v;
					}
					$list = $this->list_cast($list);
					$output = json_encode(array(
						'PageNums' =>$page,
						'data'=>$list,
						'result' => 1
					),JSON_UNESCAPED_SLASHES);
					$this->Savefile($path,$output);
				}
			}

		}else{
			$this->show_error();
		}

		$this->PushData($output);
	}
	
}