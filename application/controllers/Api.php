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
		$other["CatList"] = array(
			array(
				"CatID" => "5-4",
				"CatName" => "﻿金融High Tea",
				"MappingCatID" => "1",
			),
			array(
				"CatID" => "5-417",
				"CatName" => "﻿巴士的點評",
				"MappingCatID" => "1",
			),
			array(
				"CatID" => "5-0",
				"CatName" => "﻿Executive日記",
				"MappingCatID" => "1",
			),
		);

		
		
		$tmp = $this->topic(true);
		if($tmp){
			$catlist = array();
			foreach($tmp as $v){
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
		$this->Expired = $this->config->item('force_cache');
		$data = array();
		if(!($data=json_decode($this->Getfile($this->config->item('app_config_path')),true))||isset($_GET['gen'])){
			$data = $this->config->item("app_config");
			$this->Savefile($this->config->item('app_config_path'),json_encode($data,JSON_UNESCAPED_SLASHES));
		}
		$data['special'] = array();
		$special = $this->special(true);
		if($special){
			$data['special'] = $special;
		}
		
		$output = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
	}
	
	public function special($is_return = false){
		$this->load->model('Special');
		$special = $this->Special->get_special();
		$empty = false;
		if(count($special)>0){
			$special = $special[0];
			if($is_return){
				return $special;
			}
		}		
		else{
			$empty = true;
			if($is_return){
				return false;
			}
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
		$this->PushData($output);
		
	}
	
	public function topic($is_return = false){
		$this->load->model('Topic');
		$all_topic = $this->Topic->get_all_topic();
		$empty = false;
		if(count($all_topic)==0){
			$empty = true;
		}
		if($empty){
			if($is_return){
				return false;
			}
			$output = json_encode(array(
				'result' =>0,
			),JSON_UNESCAPED_SLASHES);
		}
		else{
			if($is_return){
				return $all_topic;
			}
			$output = json_encode(array(
				'result' =>1,
				'data' => $all_topic,
			),JSON_UNESCAPED_SLASHES);
		}
		$this->PushData($output);
		
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
	//"hit_list2" use while hit_list cannot fix problem, hit_list create too many request to DB
	public function hit_list2($section){
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
			$this->show_error();
		}
		if(!$error){
			$this->load->model('Section');
			$this->load->model('News_category_list');
			$SectionName = $this->Section->Get_Section($section)[0]->section_name;
			$data = array();
			$tmp = json_decode(file_get_contents($file),true);
			foreach($tmp as $name => $list){
				if($name != 'day'){
					continue;
				}
				foreach($list as $k=>$v){
					//top 10 only
					$s = $section;
					$is_column = $v['catID']==9 && $section == 2;
					if(count($data)==10){
						break;
					}
					if($section == 1){
						$v['newsId'] = $v['newsId'] - 500000;
					}
					$video = isset($v['video_path_1'])&&!empty($v['video_path_1'])?$v['video_path_1']:"";
					$writer = array();	
					if(isset($v['columnistID'])&&$is_column){
						$s = 5;
						// $writer = array('name'=>'test');
					}
					
					$data[] = array(
						'id' => $v['newsId'],
						'title' => $v['title'],
						'section' => $s,
						'cat'	=> $this->News_category_list->mapcat2cat($s, $v['catID']),
						'publish_datetime'=>$v['publishDatetime'],
						'vdo'=>$video,
						'writer'=>$writer,//專欄顯示
						'layout'=>"",//日報為空
					);
					$this->load->model($SectionName);
					$this->$SectionName->SetImg($data,array());
				}
			}
			$output['data'] = $this->list_cast($data);
			$output = json_encode($output,JSON_UNESCAPED_SLASHES);
			$this->PushData($output);
		}
		
			
	}

	
	
	public function hit_list($section){
		$this->Expired = $this->config->item('list_time');
		if($section == 1){
			$file = $this->config->item('instant_top_list_path');
			$sname = 'instant';
		}
		else if($section == 2){
			$file = $this->config->item('daily_top_list_path');
			$sname = 'daily';
		}
		else{
			$this->show_error();
		}
		$path = $this->config->item('hit_list_path');
		$path = str_replace('{section}',$sname,$path);
		
		if(!($data=json_decode($this->Getfile($path),true))||isset($_GET['gen'])){
			$this->load->model('Section');
			$this->load->model('News_category_list');
			$tmp = json_decode(file_get_contents($file),true);
			foreach($tmp as $name => $list){
				if($name != 'day'){
					continue;
				}
			
				$list_id = array();
				$sort_list = array();
				foreach($list as $k=>$v){
					//top 10 only
					$is_column = $v['catID']==9 && $section == 2;
					if(count($sort_list)==10){
						break;
					}
					if($section == 1){
						$v['newsId'] = $v['newsId'] - 500000;
						$list_id[$section][] = $v['newsId'];
					}
					else{
						if($is_column){
							$list_id["5"][] = $v['newsId'];
						}
						else{
							$list_id[$section][] = $v['newsId'];
						}
					}
					$sort_list[$v['newsId']] = count($sort_list);
				}
				
				$sections = array_keys($list_id);
				$data =array();
				foreach($sections as $s ){
					$SectionName = $this->Section->Get_Section($s)[0]->section_name;
					$this->load->model($SectionName);
					$list = $this->$SectionName->Get_News_list_by_ID($list_id[$s]);
					foreach($list as $key=> $value ){
						$value['section'] = $s.'';
						if(isset($value['map_cat'])){
							
							$value['cat'] = $this->News_category_list->mapcat2cat($s, $value['map_cat']);
						}
						
						$data[$sort_list[intval($value['id'])]] =  $value;
					}
				}
			}
			
			if(count($data)>0){
				$data = $this->list_cast($data);
				$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
			}
		}
		$output = array(
			'result' => 1,
			'data'	=> $data,
		);
		$output = json_encode($output,JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
			
	}

	public function interest(){

		$this->Expired = $this->config->item('interest_time');
		$path = $this->config->item('interest_list_path');
		$fileid = rand(0,9);
		$outputpath = str_replace('{page}',$fileid,$path);
		
		if(!($list=json_decode($this->Getfile($outputpath),true))||isset($_GET['gen'])){
			$this->load->model('Instant');
			$data = $this->Instant->GetInterestList();
			$file = array();
			$fileidlist = array();
			$this->load->model('News_category_list');
			foreach ($data as $key => $value) {
				$value['section'] = "1";
				$value['cat'] = $this->News_category_list->mapcat2cat("1",$value['map_cat']);
				$file[(($key+1)%10)][] = $value;
				$fileidlist[] = (($key+1)%10);
			}
			foreach ($file as $k => $v) {
				$filepath = str_replace('{page}',$k,$path);
				$data = $this->list_cast($file[$k]);
				$this->Savefile($filepath, json_encode($data,JSON_UNESCAPED_SLASHES));
				if(in_array($k,$fileidlist)&&$k==$fileid){
					$list = $data;
				}
			}
			if($list==false||$list==''){
				$this->show_error();
			}
		}
		$output = json_encode(array(
			'data'=>$list,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);		
		$this->PushData($output);
	}
	
	public function detail($section, $id){
		$this->Expired = $this->config->item('detail_time');
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
		
		$error = false;
		$error = count($res)<1;

		if(!$error){

			$section_name = $res[0]->section_name;
			$this->load->model($section_name);
			
			
			
			$path= $this->config->item('detail_path');
			$path = str_replace('{section}',$section_name,$path);
			$page = ((int)($id/1000)+1)*1000;
			$path = str_replace('{page}',$page,$path);
			$path = str_replace('{id}',$id,$path);
			if(!($data=json_decode($this->Getfile($path),true))||isset($_GET['gen'])){
				
				$data = $this->$section_name->GetDetail($id);
				
				if($data){
					$data['section'] = $section;
					if(isset($data['keyword'])&&$data['keyword']!=''){
						
						$keyword = explode(';',$data['keyword']);
						if($keyword[0]==''){
							unset($keyword[0]);
						}
						$data['keyword'] = $keyword;
					}
					else{
						$data['keyword'] = array();
					}
					if(isset($data['map_cat'])){
						$this->load->model('News_category_list');
						$data['cat'] = $this->News_category_list->mapcat2cat($section,$data['map_cat']);
					}
					if($data["section"]==1){
						$this->load->model("Topic");
						
						$data["topic"] = $this->Topic->is_topic_keyword($data["keyword"]);
					}
					$content = array("","","");
					if(isset($data['content'])){
						
						$content[0] = $data['content'];
						
						// $content[0] = str_replace($this->config->item("new_line"),"\n",$content[0]);
						// $content[0] = strip_tags($content[0]);
						
					}
					if(isset($data['content2'])){
						$content[1] = $data['content2'];
						// $content[1] = str_replace($this->config->item("new_line"),"\n",$content[1]);
						// $content[1] = strip_tags($content[1]);
					}
					if(isset($data['content3'])){
						$content[2] = $data['content3'];
						// $content[2] =str_replace($this->config->item("new_line"),"\n",$content[2]);
						// $content[2] = strip_tags($content[2]);
					}
					$data['content'] = $content;
					
					$data = $this->detail_cast($data);
					if(count($data["related_news"])>0){
						foreach($data["related_news"] as $k => $v){
							$v['section'] = $section;
							if(isset($v['map_cat'])){
								$this->load->model('News_category_list');
								$v['cat'] = $this->News_category_list->mapcat2cat($section,$v['map_cat']);
							}
							$data["related_news"][$k] = $v;
						}
						$data["related_news"] = $this->list_cast($data["related_news"]);
					}
					
					
					
					$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
					
				}
				else{
					$this->show_error();
				}

			}
		}
		else{
			$this->show_error();
		}
		
		
		
		$output = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		
		
		
		$this->PushData($output);
	}
	
	public function list($section, $cat = -1, $page =1){
		$this->Expired = $this->config->item('list_time');
		if($section == 5 && ($cat == 0 ||$cat == 4 ||$cat ==  417)){
			$this->columns($cat);
			return;
		}
		if($cat == -1 && $section != '3'){
			$this->show_error();
		}
		else if($cat == -1 && $section == '3'){
			$cat = 1;
		}
		
		if($section == "topic"){
			$this->topic_list($cat);
			return;
		}
		$this->load->model('News_category_list');
		$is_cat = $this->News_category_list->Check_Cat($section,$cat);
		if($is_cat){
			if($section == 5){
				$map_cat = $cat;
			}
			else{
				$map_cat = $this->News_category_list->cat2mapcat($section,$cat);
				$map_cat = ($map_cat==-1)?0:$map_cat;
			}
			$this->load->model('Section');
			$section_name = $this->Section->Get_Section($section)[0]->section_name;
			$this->load->model($section_name);
			$this->$section_name->page($page);
			$path = str_replace('{section}',$section_name,$this->config->item('list_path'));
			$path = str_replace('{cat}',$cat,$path);
			$path = str_replace('.json','_'.(int)$page.'json',$path);
			if(!($list=json_decode($this->Getfile($path)))||isset($_GET['gen'])){
				$list = $this->$section_name->GetList($map_cat);
				
				if($list){
					foreach($list as $k=>$v){
						// var_dump($section);exit;
						$v["section"] = $section;
						$v["cat"] = $cat;
						if(isset($v["map_cat"])){
							$v["cat"] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
						}
						$list[$k] = $v;
					}
					$list = $this->list_cast($list);
					if(count($list)>0){
						$this->Savefile($path,json_encode($list,JSON_UNESCAPED_SLASHES));
					}
				}
			}
			$output = json_encode(array(
				'PageNums' =>$page,
				'data'=>$list,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);
		}
		else{
			$this->show_error();
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
	
	public function columns($columnid){
		$this->Expired = $this->config->item('list_time');
		$this->load->model('Columns');
		
		$path = str_replace('{id}',(int)$columnid,$this->config->item('columns_path'));


		if(!($data=json_decode($this->Getfile($path),true))||isset($_GET['gen'])){

			$data = $this->Columns->column($columnid,11);
			if($data){
				foreach($data['list'] as $k =>$v){
					$data['list'][$k]['section'] = "5"; 
				}
				$data['list'] = $this->list_cast($data['list']);
				if(count($data['list'])>0){
					$this->Savefile($path,json_encode($data, JSON_UNESCAPED_SLASHES));
				}
			}
			else{
				$this->show_error();
			}
			// var_dump($data['list']);exit;
		}
		$output = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($output);

	}
	
	public function section(){

		$this->Expired = $this->config->item('force_cache');

		if(!($data=json_decode($this->Getfile($this->config->item('section_list_path')),true))||isset($_GET['gen'])){
			$this->load->model('Section');
			$data = $this->Section->Get_Section_list();
			/*
			foreach($data as $key => $value){
				foreach($value['CatList'] as $k => $cat){
					if($cat['CatID']==0){
						$Catlist = array();
						$Catlist[] = array(
							'CatID' => $cat['CatID'],
							'CatName'=>$cat['CatName'],
							'MappingCatID'=>$cat['MappingCatID'],
						);
						$data[$key]['CatList'] = $Catlist;
						
						break;
					}
				}
			}
			*/
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
			"vdo"					=> array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>''),
			"imgs"					=> array(),
			"writer"				=> array('columnTitle'=>'','columnistID'=>'','trait'=>'','writer'=>''),
			"layout"				=> "1",
		);
		foreach($data as $i => $d){
			$tmp = $list;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v;
				if($k=='vdo'&&isset($d[$k])&&is_string($d[$k]))
				{
					$tmp[$k] =  array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>$d[$k]);
				}
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
			"vdo"					=> array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>''),
			"vid"					=> array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>''),
			"imgs"					=> array(),
			"writer"				=> array('columnTitle'=>'','columnistID'=>'','trait'=>'','writer'=>''),
			"layout"				=> "1",
			"keyword"				=> array(),
			"related_news"			=> array(),
			"topic"					=> array(),
		);
		
		
		$tmp = $detail;
		foreach($tmp as $k => $v){
			$tmp[$k] = isset($data[$k])?$data[$k]:$v;
			if($k=='vdo'&&isset($data[$k])&&is_string($data[$k]))
			{
				$tmp[$k] =  array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>$data[$k]);
			}
			if($k=='vid'&&isset($data[$k])&&is_string($data[$k]))
			{
				$tmp[$k] =  array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>$data[$k]);
			}
		}
		$return_data = $tmp;
		return $return_data;
	}
	
	public function highlight()	{
		$this->Expired = $this->config->item('list_time');
		if(!($data=json_decode($this->Getfile($this->config->item('highlight_path')),true))||isset($_GET['gen'])){
			
			$this->load->model('Highlight');
			$data = $this->Highlight->Get_highlight_list();
			$data[50] = array ( 
				'id' => '81665', 
				'title' => '《鐵探》演出獲激讚              姜皓文直認恨攞視帝', 
				'section' => "3", 
				'cat' => '3', 
				'publish_datetime' => '2019-04-11 23:00:00', 
				'vdo' => 'hkheadline/instant_video/2019/0411/186c944aaab36304e61635dfacc5e488.mp4', 
				'imgs' => array ( 0 => array ( 'path' => '/2019/04/11/Img_81665_500_190411153139.jpg', 'isCover' => '1', ), ), 
			);
			$this->load->model('News_category_list');
			foreach($data as $k=>$v){
				if(isset($v['map_cat'])){
					$data[$k]['cat'] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
				}
				
				
			}
			$data = $this->list_cast($data);
			if(count($data)>0){
				$this->Savefile($this->config->item('highlight_path'),json_encode($data,JSON_UNESCAPED_SLASHES));
			}
		} 
		
		$data2 = array();
		foreach($data as $k => $v){
			if(!count($v['imgs'])==0){
				$data2[] = $data[$k];
			}
		}

		
		
		$highlight_list = json_encode(array(
			'data'=>$data2,
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
	
	public function search($keyword,$page=1){
		$this->load->model('search');
		
		$keyword = strip_tags(str_replace($this->config->item("search_filter"),"",urldecode($keyword)));
		$data = null;
		
		if(isset($keyword)||trim($keyword)){
			$search = $this->search->Getlist($keyword, $page);
			if($search&&count($search['data'])>0){
				$search['data'] = $this->list_cast($search['data']);
				$search['result'] = 1;
				$this->PushData(json_encode($search,true));
				return;
			}
		}
		
		$this->show_error();
		
	}

	public function ads($section='',$cat=''){	
		// echo MD5(MD5('123123'));
		$ads = ($section==''||(int)$section==0||$cat=='')?'index':$section.'-'.$cat;
		$this->load->model('Ads');
		$data = $this->Ads->GetAds($ads);
		$ads = array();
		if(count($data)>0){
			$ads['data'] = $data;
			$ads['result'] = 1;
			$this->PushData(json_encode($ads,true));
			return;
		}
		$this->show_error();

	}

}