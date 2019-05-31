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
				
				"publishtime"=>"desc",
			)
		);
		$response = $this->solr->search($searchCountArr);
		if(isset($response['responseHeader']['status']) && $response['responseHeader']['status'] == 0){
			$numOfFound = (isset($response['response']['numFound']))?$response['response']['numFound']:0;
			$resultArray = (isset($response['response']['docs']))?$response['response']['docs']:array();
		}
		
		$this->load->model('News_category_list');
		$data = array();
		foreach($resultArray as $v){
			$data[$v['section']][] = $v['id'];
		}
		$this->load->model('Section');
		$return_data = array();
		foreach ($data as $key => $value) {
			$section_info = $this->Section->Get_Section($key);
			if($section_info)
			{
				$section_name = $section_info[0]->section_name;
				$this->load->model($section_name);
				$tmp = $this->$section_name->Get_News_list_by_ID($value);
				foreach($tmp as $i => $d) {
					$tmp[$i]['section'] = $section_info[0]->section_id;
				}
				$return_data = array_merge($return_data,$tmp);
			}
		}
		return array(
			'page_size'	 => $rows,
			'page_md5' => md5(json_encode($data)),
			'page_now' => $page,
			'data'	=> $return_data
		);
	}
	
	
}
