<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Highlight extends My_Model  {
	
	
	public function __construct (){
		$this->mainDB = 'hl_app';
		parent::__construct();
	}
	
	public function Get_highlight($section=null){

		$this->db->from('hl_app_highlight');
		if($section!=null)
		{
			$this->db->where('section_id',$section);
		}
		$this->db->where('status',1);
		$this->db->order_by('pos','asc');
		$res = $this->db->get();

		return $res->result_array();
	}
	public function Get_pos_highlight()
	{
		$this->db->from('hl_app_pos_highlight');
		$this->db->where('status',1);
		$this->db->order_by('pos','asc');
		$res = $this->db->get();

		return $res->result_array();

	}
	public function Get_pos_highlight_list(){

		$list = $this->Get_pos_highlight();
		$request_data = array();
		$this->load->model('Section');
		foreach ($list as $key => $value) {
			$section_info = $this->Section->Get_Section($value['session_id']);
			if($section_info)
			{
				$section_name = $section_info[0]->section_name;
				$this->load->model($section_name);
				$data = $this->$section_name->Get_frist_New($value['cat']);
				if(count($data)>=1)
				{
					$data['section'] = $section_info[0]->section_id;
					$request_data[$value['pos']] = $data;
				}
			}
		}
		return $request_data;
	}
	public function Get_highlight_list(){
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