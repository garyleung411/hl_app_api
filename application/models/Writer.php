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
		
		public function GetWriter($id,$year)
		{
			$data = array();
			if(is_array($year))
			{
				foreach ($year as $value) {
					# code..}
					$this->db->select('nw.columnistID,nw.columnTitle,nw.writer,nw.trait,dhn.dailyID as id');
					$this->db->from('news_extra_base_'.$value.' as neb');
					$this->db->join('daily_hl_extra_'.$value.' as dhe','neb.extraID = dhe.extraID');
					$this->db->join('news_writer_list as nw','dhe.columnistID = nw.columnistID');
					$this->db->join('daily_hl_news as dhn','dhn.newsID = neb.newsID AND dhn.year = '.$value, 'inner');
					if(is_array($id)){
						$this->db->where_in('dhn.dailyID',$id);
					}else{
						$this->db->where('dhn.dailyID',$id);
					}

					$res = $this->db->get();
					foreach ($res->result_array() as $v){
						$i = $v['id'];
						unset($v['id']);
						$data[$i] = $v;
					}
				}
			}else{
				$this->db->select('nw.columnistID,nw.columnTitle,nw.writer,nw.trait,dhn.dailyID as id');
				$this->db->from('news_extra_base_'.$year.' as neb');
				$this->db->join('daily_hl_extra_'.$year.' as dhe','neb.extraID = dhe.extraID');
				$this->db->join('news_writer_list as nw','dhe.columnistID = nw.columnistID');
				$this->db->join('daily_hl_news as dhn','dhn.newsID = neb.newsID AND dhn.year = '.$year, 'inner');
				if(is_array($id)){
					$this->db->where_in('dhn.dailyID',$id);
				}else{
					$this->db->where('dhn.dailyID',$id);
				}

				$res = $this->db->get();
				foreach ($res->result_array() as $v){
					$i = $v['id'];
					unset($v['id']);
					$data[$i] = $v;
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