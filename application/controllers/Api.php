<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends DefaultApi{
	public $gen = false;
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		$this->gen = isset($_GET['gen']);
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
				"icon" => "http://static.stheadline.com/stheadline/columnist_res/columnist_65x65/20120405050334300476075.jpg",
			),
			array(
				"CatID" => "5-417",
				"CatName" => "﻿巴士的點評",
				"MappingCatID" => "1",
				"icon" => "http://static.stheadline.com/stheadline/columnist_res/columnist_65x65/20150327042040701019288.jpg",
			),
			array(
				"CatID" => "5-0",
				"CatName" => "﻿Executive日記",
				"MappingCatID" => "1",
				"icon" => "http://static.stheadline.com/stheadline/columnist_res/columnist_65x65/20120405045803512634278.jpg",
			),
		);
		$this->load->model('News_category_list');
		$life_cat = $this->News_category_list->Get_Cat('4');
		foreach($life_cat as $i => $cat){
			$icons = array(
				"icon-life-travel.png",
				"icon-life-dining.png",
				"icon-life-digital.png",
				"icon-life-car.png",
				"icon-life-fashion.png",
				"icon-life-living.png",
			);
			$other["CatList"][] = array(
				"CatID" => ("4-".$cat->cat_id) ,
				"CatName" => $cat->cat_cname,
				"MappingCatID" => $cat->mapping_catid,
				"icon" => 'https://hlapp.stheadline.com/images/life/'.$icons[$i],
			);
			
		}
		
		
		$tmp = $this->topic(true);
		$catlist = array();
		if($tmp){
			foreach($tmp as $v){
				$cat = array(
					"CatID" => $v['id'],
					"CatName" => $v['title'],
					"MappingCatID" => "",
					"icon" => $this->config->item('hl_app_img_url').$v['icon'],
				);
				$catlist[] = $cat;
			}
		}
		$topic = array(
			"SectionID" => "topic",
			"name" => "話題新聞",
			"SectionName" => "topic",
			"CatList"	=> $catlist,
		);
		return array($other, $topic);
		
	}
	
	public function app_config(){
		/**
		 *	Code for update app_config
		 */
		$this->Expired = $this->config->item('force_cache');
		$data = array();
		if(!($data=json_decode($this->Getfile($this->config->item('app_config_path')),true))||$this->gen){
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
			$this->show_error(2);
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

		if(count($all_topic)==0){
			if($is_return){
				return false;
			}
			$this->show_error(2);
		}
		if($is_return){
			return $all_topic;
		}
		$output = json_encode(array(
			'result' =>1,
			'data' => $all_topic,
		),JSON_UNESCAPED_SLASHES);
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
			$this->show_error(3);
		}
		$path = $this->config->item('hit_list_path');
		$path = str_replace('{section}',$sname,$path);
		
		if(!($data=json_decode($this->Getfile($path),true))||$this->gen){
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
						if($value['section']==5){
							$value['cat'] = '1';
						}
						$data[$sort_list[intval($value['id'])]] =  $value;
					}
				}
			}
			
			if(count($data)>0){
				$data = $this->list_cast($data);
				$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
			}
			else{
				$this->show_error(2);
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
		
		if(!($list=json_decode($this->Getfile($outputpath),true))||$this->gen){
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
				$this->show_error(2);
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
			$page = ((int)((int)$id/1000)+1)*1000;
			$path = str_replace('{page}',$page,$path);
			$path = str_replace('{id}',$id,$path);
			if(!($data=json_decode($this->Getfile($path),true))||$this->gen){
				
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
					if($data["section"]==5){
						$data["cat"] = "1";
					}
					if($data["section"]==1){
						$this->load->model("Topic");
						$data["topic"] = $this->Topic->is_topic_keyword($data["keyword"]);
					}
					$content = array("","","");
					if(isset($data['content'])){
						
						$content[0] = $data['content'];
						if($section == '5' || $section == '2'){
							$content[0] = str_replace("\n","<br /><br />",$content[0]);
						}
						// $content[0] = str_replace($this->config->item("new_line"),"\n",$content[0]);
						// $content[0] = strip_tags($content[0]);
						
					}
					if(isset($data['content2'])){
						$content[1] = $data['content2'];
						if($section == '5' || $section == '2'){
							$content[1] = str_replace("\n","<br /><br />",$content[1]);
						}
						// $content[1] = str_replace($this->config->item("new_line"),"\n",$content[1]);
						// $content[1] = strip_tags($content[1]);
					}
					if(isset($data['content3'])){
						$content[2] = $data['content3'];
						if($section == '5' || $section == '2'){
							$content[2] = str_replace("\n","<br /><br />",$content[2]);
						}
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
							if($v['section']==5){
								$v['cat'] = '1';
							}
							$data["related_news"][$k] = $v;
						}
						$data["related_news"] = $this->list_cast($data["related_news"]);
					}
					if(count($data["relevant_news"])>0){
						foreach($data["relevant_news"] as $k => $v){
							$v['section'] = $section;
							$v['cat'] = $this->News_category_list->mapcat2cat($v['section'] ,$v['map_cat']);
							$v = $this->detail_cast($v);
							$data["relevant_news"][$k] = $v;	
						}
					}
					
					
					$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
					
				}
				else{
					$this->show_error(2);
				}

			}
		}
		else{
			$this->show_error(3);
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
			$this->show_error(3);
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
			$path = str_replace('.json','_'.(int)$page.'.json',$path);
			if(!($list=json_decode($this->Getfile($path)))||$this->gen){
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
					else{
						$this->show_error(2);
					}
				}
				else{
					$this->show_error(2);
				}
			}
			$output = json_encode(array(
				'PageNums' =>$page,
				'data'=>$list,
				'result' => 1
			),JSON_UNESCAPED_SLASHES);
		}
		else{
			$this->show_error(3);
		}

		$this->PushData($output);
	}
	
	private function topic_list($cat){
		$empty = false;
		$this->load->model('Topic');
		$list = array();
		if($cat > $this->config->item('total_topic')){
			$this->show_error(3);
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
		
		if($empty){
			$this->show_error(2);
		}
		else{
			$this->load->model('News_category_list');
			foreach($list as $k => $v){
				$v['section'] = 'topic';
				if(isset($v['map_cat'])){
					$v['cat'] = $this->News_category_list->mapcat2cat(1,$v['map_cat']);
				}
				$list[$k] = $v;
			}
			$list = $this->list_cast($list);
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


		if(!($data=json_decode($this->Getfile($path),true))||$this->gen){

			$data = $this->Columns->column($columnid,11);
			if($data){
				foreach($data['list'] as $k =>$v){
					$data['list'][$k]['section'] = "5"; 
					$data['list'][$k]['cat'] = "1"; 
					$data['list'][$k]['publish_datetime'] = date('Y-m-d', strtotime($data['list'][$k]['publish_datetime']));
				}
				$data['list'] = $this->list_cast($data['list']);
				if(count($data['list'])>0){
					$this->Savefile($path,json_encode($data, JSON_UNESCAPED_SLASHES));
				}
			}
			else{
				$this->show_error(2);
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

		if(!($data=json_decode($this->Getfile($this->config->item('section_list_path')),true))||$this->gen){
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
			"newsID"				=> "",
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
				// if($k=="title"){
					// $tmp[$k] = str_ireplace("\n","",$tmp[$k]);
				// }
			}
			$return_data[] = $tmp;
		}
		return $return_data;
	}
	
	private function detail_cast($data){
		$return_data = array();
		$detail = array(
			"id"					=> "",
			"newsID"				=> "",
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
			"relevant_news"			=> array(),
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
			// if($k=="title"){
				// $tmp[$k] = str_ireplace("\n","",$tmp[$k]);
			// }
		}
		$return_data = $tmp;
		return $return_data;
	}
	
	public function highlight()	{
		$this->Expired = $this->config->item('list_time');
		if(!($data=json_decode($this->Getfile($this->config->item('highlight_path')),true))||$this->gen){
			
			$this->load->model('Highlight');
			$data = $this->Highlight->Get_highlight_list();
			$this->load->model('News_category_list');
			foreach($data as $k=>$v){
				if(isset($v['map_cat'])){
					$data[$k]['cat'] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
				}
				if($v['section']==5){
					$data[$k]['cat'] = '1';
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
		if(count($data2)<1){
			$this->show_error(2);	
		}
		
		
		$highlight_list = json_encode(array(
			'data'=>$data2,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($highlight_list);

	}
	
	public function sp_search(){
		
		$list = getPostVal('data');
		
		if(!isset($list)){
			$this->show_error(3);
		}
		if(!is_array($list )){
			$list = json_decode($list,true);
		}
		
	
		$this->load->model('Sp_search');
		
		$data = $this->Sp_search->Get_list_by_id($list);
		$this->load->model('News_category_list');
		foreach($data as $k=>$v){
			if(isset($v['map_cat'])){
				$data[$k]['cat'] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
			}	
		}
		$data = $this->list_cast($data);
		if(count($data)<1){
			$this->show_error(2);	
		}
		$output = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
	}
	
	public function search($keyword,$page=1){
		$this->load->model('search');
		
		$keyword = strip_tags(str_replace($this->config->item("search_filter"),"",urldecode($keyword)));
		$data = null;
		
		if(isset($keyword)&&strlen(trim($keyword))>=1){
			$search = $this->search->Getlist($keyword,(int)$page);
			if($search&&count($search['data'])>0){
				$search['data'] = $this->list_cast($search['data']);
				$search['result'] = 1;
				$this->PushData(json_encode($search,true));
				return;
			}
			else{
				$search['data'] = $this->list_cast($search['data']);
				$search['result'] = 1;
				$this->PushData(json_encode($search,true));
				return;
			}
		}
		
		$this->show_error(3);
		
	}
	
	public function ads_section(){	
		$ads_section = $this->config->item('ads_cat_list_pos');
		
		$ads['data'] = $ads_section;
		$ads['result'] = 1;
		$this->PushData(json_encode($ads,true));
		return;
	}
	
	public function ads($section,$cat=''){	
		// echo MD5(MD5('123123'));
		$ads = ($cat=='')?$section:$section.'-'.$cat;
		$pdate = getGetVal('pdate');
		$pdate = !empty($pdate)&&isset($pdate)?$pdate:date('Y-m-d');
		$this->load->model('Ads');
		$data = $this->Ads->GetAds($ads, $pdate);
		$ads = array();
		if(count($data)>0){
			$ads['data'] = $data;
			$ads['result'] = 1;
			$this->PushData(json_encode($ads,true));
			return;
		}
		$this->show_error(2);
	}
	
	public function show_404(){
		$this->show_error(404);
	}
}
