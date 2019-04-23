<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Highlight extends CI_Model  {
	
	
	public function __construct (){
		parent::__construct();
		$this->load->database('hl_app');
	}
	
	public function Get_highlight($section=null){

		$this->db->from('hl_app_highlight');
		if($section!=null)
		{
			$this->db->where('section_id',$section);
		}
		$res = $this->db->get();

		return $res->result_array();
	}

	public function Get_highlight_list()
	{
		$list = $this->Get_highlight();
		$list_id = array();//根据session分类
		$list_order_by = array();//对应排序
		$data = array();
		foreach ($list as $key => $value) {
			$list_id[$value['section_id']][] = $value['newsID'];
			$list_order_by[$value['newsID']] = $value['id'];
		}
		$this->load->model('Section');

		foreach ($list_id as $key => $value) {
			$section_info = $this->Section->Get_Section($key);
			if($section_info)
			{
				$section_name = $section_info[0]->section_name;
				$this->load->model($section_name);
				// $this->$section_name->SetSectionId($key);
				$tmp = $this->$section_name->Get_highlight_News_list($value);
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