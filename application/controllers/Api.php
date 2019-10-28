<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends DefaultApi{
	
	public $platform;
	public $platform_type = array(
		"android"=>"_android",
		"ios"=>"_ios",
	);
	
	
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		// $this->load->library('session');
	}
	
	public function app_config(){
		/**
		 *	Code for update app_config
		 */
		$this->Expired = $this->config->item('force_cache');
		$path = $this->config->item('app_config_path');
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
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
			$special['special_icon'] = $this->config->item('hl_app_img_url').$special['special_icon'];
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
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
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
							if(count($value['imgs'])==0){
								$this->load->model('Writer');
								$value['imgs'] = array(0=>array());
								$value['imgs'][0]['isCover'] = 0;
								$value['imgs'][0]['path'] = $this->Writer->GetLarge_Cover_by_ID($value['writer']['columnistID'])[0]['largeCover'];
							}
							$value['cat'] = '1';
						}
						$data[$sort_list[intval($value['id'])]] =  $value;
					}
				}
			}
			
			if(count($data)>0){
				$data = $this->list_cast($data);
			}
			else{
				$data = json_decode($this->getFile($path, true),true);
			}
			$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
			
		}
		if(!$data){
			$this->show_error(2);
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
		$data = json_decode($this->getFile($outputpath, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
			$this->load->model('Instant');
			$interest = $this->Instant->GetInterestList();
			$file = array();
			$fileidlist = array();
			$this->load->model('News_category_list');
			foreach ($interest as $key => $value) {
				$value['section'] = "1";
				$value['cat'] = $this->News_category_list->mapcat2cat("1",$value['map_cat']);
				$file[(($key+1)%10)][] = $value;
				$fileidlist[] = (($key+1)%10);
			}
			foreach ($file as $k => $v) {
				$filepath = str_replace('{page}',$k,$path);
				$interest = $this->list_cast($file[$k]);
				if($interest){
					$this->Savefile($filepath, json_encode($interest,JSON_UNESCAPED_SLASHES));
				}
				if(in_array($k,$fileidlist)&&$k==$fileid){
					$data = $interest;
				}
			}
		}
		if(!$data){
			$this->show_error(2);
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
		$path= $this->config->item('detail_path');
		$path = str_replace('{section}','s'.$section,$path);
		$page = ((int)((int)$id/1000)+1)*1000;
		$path = str_replace('{page}',$page,$path);
		$path = str_replace('{id}',$id,$path);
		
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
			$this->load->model('Section');
			$res = $this->Section->Get_Section($section);
			if(count($res )>0){
				$section_name = $res[0]->section_name;
				$this->load->model($section_name);
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
					$data['share_link'] = $this->share_link($data);
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
							// $v = $this->detail_cast($v);
							$data["relevant_news"][$k] = $v;	
						}
						$data["relevant_news"] = $this->list_cast($data["relevant_news"]);
					}	
				}
				if($data){
					$this->saveFile($path,json_encode($data,ENT_QUOTES));
				}
			}
			else{
				$this->show_error(3);
			}
		}
		if(!$data){
			$this->show_error(2);
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
		$path = str_replace('{section}','s'.$section,$this->config->item('list_path'));
		$path = str_replace('{cat}',$cat,$path);
		$path = str_replace('.json','_'.(int)$page.'.json',$path);
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
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
				$data = $this->$section_name->GetList($map_cat);	
				if($data){
					foreach($data as $k=>$v){
						// var_dump($section);exit;
						$v["section"] = $section;
						$v["cat"] = $cat;
						if(isset($v["map_cat"])){
							$v["cat"] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
						}
						if($v["section"] == 3){
							$v['share_link'] = $this->share_link($v);
						}
						$data[$k] = $v;
					}
					$data = $this->list_cast($data);
				}
				else{
					$data=json_decode($this->Getfile($path, true),true);
				}
				if(count($data)>0){
					$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
				}
				
			}
			else{
				$this->show_error(3);
			}
		}
		if(!$data){
			$this->show_error(2);
		}
		$output = json_encode(array(
			'PageNums' =>$page,
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);

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


		if(!($data=json_decode($this->Getfile($path),true))||$this->gen()){
			$rows = $this->config->item('total_columns_list_item');
			$data = $this->Columns->column($columnid, $rows);
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
				else{
					$data=json_decode($this->Getfile($path, true),true);
				}
			}
			else{
				$data=json_decode($this->Getfile($path, true),true);
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
		$path = $this->config->item('section_list_path');
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
			$this->load->model('Section');
			$data = $this->Section->Get_Section_list();		
			if(count($data)>0){
				$extra = $this->index_section();
				foreach($extra as $v){
					$data[] = $v;
				}
			}
			else{
				$data = json_decode($this->getFile($path, true),true);
			}
			if(count($data)>0){
				$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
			}
		}
		if(!$data){
			$this->show_error(2);
		}
		$section_list = json_encode(array(
			'data'=>$data,
			'result' => 1
		),JSON_UNESCAPED_SLASHES);
		$this->PushData($section_list);
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
		$icons = array(
			"icon-life-travel.png",
			"icon-life-dining.png",
			"icon-life-digital.png",
			"icon-life-car.png",
			"icon-life-fashion.png",
			"icon-life-living.png",
		);
		foreach($life_cat as $i => $cat){
			
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
			"share_link"			=> '',
		);
		foreach($data as $i => $d){
			$tmp = $list;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v;
				if($k=='vdo'&&isset($d[$k])&&is_string($d[$k]))
				{
					$tmp[$k] =  array('cover_path'=>'','headline'=>'','id'=>'','video_path'=>$d[$k]);
				}
				if($k=="content"){
					$tmp[$k] =  preg_replace('/^　　(.*)?/', '$1', $tmp[$k]);//\u3000
					$tmp[$k] =  preg_replace('/^  (.*)?/', '$1', $tmp[$k]);//\u2003;
					$tmp[$k] =  preg_replace('/^\s+(.*)?/', '$1', $tmp[$k]);
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
			"share_link"			=> '',
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
			if($k=="imgs"){
				foreach($tmp[$k] as $i => $img){
					// $img['caption'] = str_replace(array("\n", "\r"),"",$img['caption']);
					$img['caption'] = rtrim($img['caption']);
					$tmp[$k][$i] = $img;
					
				}
				// $tmp[$k] = str_ireplace("\n","",$tmp[$k]);
			}
		}
		$return_data = $tmp;
		return $return_data;
	}
	
	public function highlight()	{

		//需要获取固定位higlight
		$this->Expired = $this->config->item('list_time');
		$path = $this->config->item('highlight_path');
		$data = json_decode($this->getFile($path, $this->config->item('cache_only')),true);
		if(!$this->config->item('cache_only') && (!$data || $this->gen()) ){
			$this->load->model('Highlight');
			$data = $this->Highlight->Get_highlight_list();
			if(count($data)>0){
				$posdata = $this->Highlight->Get_pos_highlight_list();
				$return_data = array();
				$num = count($data)+count($posdata);
				for($i=0;$i<$num;$i++)
				{
					if(isset($posdata[$i+1]))
					{
						$return_data[] = $posdata[$i+1];
					}else{
						$return_data[] = array_shift($data);
					}
				}
				// return;
				$this->load->model('News_category_list');
				foreach($return_data as $k=>$v){
					if(isset($v['map_cat'])){
						$return_data[$k]['cat'] = $this->News_category_list->mapcat2cat($v['section'],$v['map_cat']);
					}
					if($v['section']==5){
						$return_data[$k]['cat'] = '1';
					}
				}
				
				$data = $this->list_cast($return_data);
			}
			else{
				$data = json_decode($this->getFile($path, true),true);
			}
			if(count($data)>0){
				$this->Savefile($path,json_encode($data,JSON_UNESCAPED_SLASHES));
			}
		} 
		if(!$data){
			$this->show_error(2);
		}
		$data2 = array();
		foreach($data as $k => $v){
			if(!count($v['imgs'])==0||$v['section']==5){
				if($v['section']==5 && count($v['imgs'])==0){
					$this->load->model('Writer');
					$data[$k]['imgs'] = array(0=>array());
					
					$data[$k]['imgs'][0]['isCover'] = 0;
					$data[$k]['imgs'][0]['path'] = $this->Writer->GetLarge_Cover_by_ID($v['writer']['columnistID'])[0]['largeCover'];
		
				}
				
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
		
		$this->platform = getGetVal('platform');
		
		if(empty($this->platform)||!in_array($this->platform,array_keys($this->platform_type))){
			$this->show_error(3);
		}
		$this->platform = $this->platform_type[$this->platform];
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
	
	private function share_link($data){
        //1=instant, 2=daily, 3=pop, 4=life, 5=columns
        $shareLink = "";
        $dailyPreLink   = "http://hd.stheadline.com/news/daily/";//http://hd.stheadline.com/news/daily/ls/764017/
        $instantPreLink = "http://hd.stheadline.com/news/realtime/";//http://hd.stheadline.com/news/realtime/hk/1501462/
        $lifePreLink    = "http://hd.stheadline.com/life/";
        $entPreLink     = "http://hd.stheadline.com/life/ent/";//http://hd.stheadline.com/life/ent/realtime/1501459/
        $popPreLink     = "http://pop.stheadline.com/content.php?";//http://pop.stheadline.com/content.php?vid=82849&cat=b
        $preColumnsLink = "http://hd.stheadline.com/news/columns/";
		/***即時***/
        $InstantList = array(
			"1"=>"hk",
			"2"=>"ent",
			"3"=>"chi",
			"4"=>"wo",
			"5"=>"pp",
			"6"=>"fin",
			"7"=>"spt",
		);
        /***即時***/

        /***日報***/
        $DailyList = array(
			"1"=>"hk",
			"2"=>"chi",
			"3"=>"wo",
			"4"=>"pp",
			"5"=>"fin",
			"6"=>"spt",
			"7"=>"ls",
			"8"=>"ent",
			"9"=>"rac",
		);
        /***日報***/

        /***日報副刊***/
        $LiftList = array(
			"1"=>"travel",
			"2"=>"dining",
			"3"=>"digital",
			"4"=>"car",
			"5"=>"fashion",
			"6"=>"living",
		);
        /***日報副刊***/

        /***POPNEWS***/
        $PopList = array(
			"1"=>"new",
			"2"=>"a",
			"3"=>"d",
			"4"=>"f",
			"5"=>"e",
			"6"=>"b",
			"7"=>"c",
			"8"=>"m",
			"9"=>"l",
			"10"=>"s",
        );
		/***POPNEWS***/
        switch($data['section']){
            case "1":
                $data['id']  += 500000;
                if($data['cat']==2){//ent special handle
                    $shareLink = $entPreLink."realtime/".$data['id'];
                }else{
                    $shareLink = $instantPreLink.$InstantList[$data['cat']]."/".$data['id'];
                }
                break;
            case "2":
                if($data['cat']=="8"){//ent special handle
                    $shareLink = $entPreLink."daily/".$data['id'];
                }else{
                    $shareLink = $dailyPreLink.$DailyList[$data['cat']]."/".$data['id'];
                }
                break;
            case "3":
                $shareLink = $popPreLink."vid=".$data['id']."&cat=".$PopList[$data['cat']];
                break;
            case "4":
                $shareLink = $lifePreLink.$LiftList[$data['cat']]."/".date('Ymd',strtotime($data['publish_datetime']))."/".$data['newsID']."/";
                break;
            case "5":
                $shareLink = $preColumnsLink.$data['writer']['columnistID']."/".date('Ymd',strtotime($data['publish_datetime']))."/".$data['id'];
                break;
        }
        return $shareLink;

	}
	
	//Trigger by incorrect route  
	public function show_404(){
		$this->show_error(404);
	}
}
