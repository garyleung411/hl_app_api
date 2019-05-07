<?php

class Search extends CI_Model
{

	public $year;
	public $date;
	public $last_date;
	public $pagesize = 50;
	public function __construct(){
		$this->date = date('Y-m-d');
		$this->last_date = date('Y-m-d',strtotime("-3 month"));
		$year = date('Y');
		$previous_year = date('Y',strtotime("-3 month"));
		if($year!=$previous_year){
			$this->year = array($year,$previous_year);
		}else{
			$this->year = array($year);
		}
	}
	
	public function Getlist($keyword, $page=1){
		$rows = $this->config->item('search_list_item');
		$lowlimit = $rows * ($page-1);
		$numOfFound = 0;
		$resultArray = array();
		$this->load->library('Solr', $this->config->item('solr'));
		$searchCountArr = array(
			"keyword"=>('"'.$keyword.'"'),
			"fields"=>array(
				"title",
				"content"
			),
			"start"=>$lowlimit,
			"rows"=>$rows,
			"sort"=>array(
				"publishtime"=>"desc"
			)
		);
		$response = $this->solr->search($searchCountArr);
		if(isset($response['responseHeader']['status']) && $response['responseHeader']['status'] == 0){
			$numOfFound = (isset($response['response']['numFound']))?$response['response']['numFound']:0;
			$resultArray = (isset($response['response']['docs']))?$response['response']['docs']:array();
		}
		
		$data = array();
		$this->load->model('News_category_list');
		foreach($resultArray as $k => $v){
			$publish_datetime = date('Y-m-d', strtotime(explode("aa", $v['publishtime'])[0]));
			$day_before = $this->config->item("column_day_before");
			$day = date('Y-m-d', strtotime("-$day_before day"));
			if(strtotime($publish_datetime) <  strtotime($day)){
				continue;	
			}
			
			$main_data = explode('@', $v["ID"]);
			$section = $main_data[2]=="instant" ? '1' : (isset($main_data[3])&&trim($main_data[3])?'5':'2');
			$id = $section==1?($main_data[0]-500000) :$main_data[0];
			$row = array(
				"id"					=> $id,
				"title"					=> $v['title'],
				"content"				=> mb_substr(strip_tags($v['content']),0,50,'utf-8'),
				"section"				=> $section,
				"publish_datetime"		=> $publish_datetime,
				"layout"				=> "1",
			);
			$row["cat"] = $this->News_category_list->mapcat2cat($row["section"], $main_data[1]);
			
			$data[] = $row;
		}
		
		return array(
			'page_size'	 => $rows,
			'page_md5' => md5(json_encode($data)),
			'page_now' => $page,
			'data'	=> $data
		);
	}
	
	
}
