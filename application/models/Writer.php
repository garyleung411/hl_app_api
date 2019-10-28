<?php
/**
*	獲取發佈者
*/
defined('BASEPATH') OR exit('No direct script access allowed');
	class Writer extends My_Model{
		public function __construct()
		{
			$this->mainDB = 'daily';
			parent::__construct();
		}
		
		public function GetWriter($newsid,$year)
		{
			$data = array();
			if(is_array($year))
			{
				foreach ($year as $value) {
					# code..}
					$this->db->select('nw.columnistID,nw.columnTitle,nw.writer,nw.trait,neb.newsid');
					$this->db->from('news_extra_base_'.$value.' as neb');
					$this->db->join('daily_hl_extra_'.$value.' as dhe','neb.extraID = dhe.extraID');
					$this->db->join('news_writer_list as nw','dhe.columnistID = nw.columnistID');
					if(is_array($newsid)){
						$this->db->where_in('neb.newsID',$newsid);
					}else{
						$this->db->where('neb.newsID',$newsid);
					}

					$res = $this->db->get();
					foreach ($res->result_array() as $v){
						$newsid = $v['newsid'];
						unset($v['newsid']);
						$data[$newsid] = $v;
					}
				}
			}else{
				$this->db->select('nw.columnistID,nw.columnTitle,nw.writer,nw.trait,neb.newsid');
				$this->db->from('news_extra_base_'.$year.' as neb');
				$this->db->join('daily_hl_extra_'.$year.' as dhe','neb.extraID = dhe.extraID');
				$this->db->join('news_writer_list as nw','dhe.columnistID = nw.columnistID');
				if(is_array($newsid)){
					$this->db->where_in('neb.newsID',$newsid);
				}else{
					$this->db->where('neb.newsID',$newsid);
				}

				$res = $this->db->get();
				foreach ($res->result_array() as $v){
					$newsid = $v['newsid'];
					unset($v['newsid']);
					$data[$newsid] = $v;
				}
			}
			return $data;
		}

		public function GetWriter_by_ID($id)
		{
			$this->db->select('columnistID,columnTitle,writer,trait');
			$this->db->from('news_writer_list');
			if(is_array($id)){
				$this->db->where_in('columnistID',$id);
			}else{
				$this->db->where('columnistID',$id);
			}
			$this->db->where('status',1);
			$res = $this->db->get();
			return $res->result_array();
		}
		
		public function GetLarge_Cover_by_ID($id)
		{
			$this->db->select('largeCover');
			$this->db->from('news_writer_list');
			if(is_array($id)){
				$this->db->where_in('columnistID',$id);
			}else{
				$this->db->where('columnistID',$id);
			}
			$this->db->where('status',1);
			$res = $this->db->get();
			return $res->result_array();
		}
	}