<?php

class Sp_search extends CI_Model
{

	public function __construct (){
		parent::__construct();
	}
	
	public function Get_list_by_id($list){
		var_dump($list);
		$list_id = array();//根据session分类
		$list_order_by = array();//对应排序
		$data = array();
		foreach ($list as $key => $value) {	
			$section = array_keys($value)[0];
			$news_id = $value[$section];
			
			$list_id[$section][] = $value[$section];
			$list_order_by[$news_id] = $key;
		}
		$this->load->model('Section');
		
		foreach ($list_id as $key => $value) {
			$section_info = $this->Section->Get_Section($key);
			if($section_info)
			{
				$section_name = $section_info[0]->section_name;
				$this->load->model($section_name);
				$tmp = $this->$section_name->Get_News_list_by_ID($value);
				foreach($tmp as $k => $v) {
					$tmp[$k]['section'] = $section_info[0]->section_id;
				}
				$data = array_merge($data,$tmp);
			}
		}
	
		$sorting = array();

		foreach ($data as $value) {
			
			$sorting[$list_order_by[$value['id']]] = $value;
		}
		ksort($sorting);

		$request_data = array();

		foreach ($sorting as  $v) {
			$request_data[] = $v;
		}

		return $request_data;
		
	}
	
	
}
