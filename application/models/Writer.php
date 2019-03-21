<?php
/**
*	獲取發佈者
*/
	class Writer extends CI_Model{
		
		public function __construct()
		{
			parent::__construct();
			$this->db = $this->load->database('daily',TRUE);
			
		}
		public function GetWriter($creatId)
		{
			$this->db->select('columnistID,writer,trait');
			$this->db->form('news_writer_list');
			$this->db->where('status',1);
            if(is_array($creatId)&&count($creatId)>1){
                $this->db->where_in('columnistID',$creatId);
            }else{
                $this->db->where('columnistID',$creatId);
            }
			$res = $this->db->get();
			return $res->result();
		}
	}