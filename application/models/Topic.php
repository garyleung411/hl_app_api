<?php

class Topic extends CI_Model
{

    public function __construct(){
    	$this->db = $this->load->database('hl_app',TRUE);
	
	}

	 public function get_all_topic(){
		$total = $this->config->item('total_topic');
		$results = $this->db->query("SELECT `id`, `title`, `icon`, `keyword`, `publish_datetime`, `end_datetime`, `status` FROM `topic` WHERE `status` = 1 AND '".date('Y-m-d')."' BETWEEN `publish_datetime` AND `end_datetime` ORDER BY `id` LIMIT $total ");
		return $results->result_array();
    }
	
	public function is_topic($keywords){
		$total = $this->config->item('total_topic');
		$results = $this->db->query("SELECT `id`, `title`, `keyword` FROM `topic` WHERE `status` = 1 AND '".date('Y-m-d')."' BETWEEN `publish_datetime` AND `end_datetime` ORDER BY `id` LIMIT $total ");
		$topic = $results->result_array();
		$data = array();
		foreach($topic as $v){
			
			if(in_array($v['keyword'],$keywords)){
				$data[] = $v;
			}
		}
		return $data;
    }

}