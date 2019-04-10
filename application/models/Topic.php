<?php

class Topic extends CI_Model
{

    public function __construct(){
    	
	
	}

	 public function get_all_topic(){
		$this->db = $this->load->database('hl_app',TRUE);
		$total = $this->config->item('total_topic');
		$results = $this->db->query("SELECT `id`, `title`, `icon`, `keyword`, `publish_datetime`, `end_datetime`, `status` FROM `topic` WHERE `status` = 1 AND '".date('Y-m-d')."' BETWEEN `publish_datetime` AND `end_datetime` ORDER BY `id` LIMIT $total ");
		return $results->result_array();
    }
	
	public function is_topic_keyword($keywords){
		$this->db = $this->load->database('hl_app',TRUE);
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
	
	public function get_topic_list_by_keyword($keyword){
		$total = $this->config->item('total_list_item');
		$day_before = $this->config->item('day_before');
		$day = date('Y-m-d',strtotime("today - $day_before days"));
		$year1 = date('Y',strtotime("today"));
		$year2 = date('Y',strtotime($day));
		
		
		$this->db = $this->load->database('instant',TRUE);	
		$results = $this->db->query("SELECT datetime, rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo,keyword 
		FROM `st_inews_main_$year1` 
		WHERE (`keyword` LIKE '%;$keyword;%' OR `keyword` LIKE '$keyword;%' OR `keyword` LIKE '%;$keyword' OR `keyword` LIKE '$keyword') AND `status` =1 AND `publish_datetime` >= $day ORDER BY `publish_Datetime` DESC LIMIT $total");
		
		$list1 = $results->result_array();
		$count = count($list1);
		
		if($count  < $total && $year1 !== $year2){
			$total = $total - $count;
			$results = $this->db->query("SELECT datetime, rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo,keyword 
			FROM `st_inews_main_$year2` 
			WHERE (`keyword` LIKE '%;$keyword;%' OR `keyword` LIKE '$keyword;%' OR `keyword` LIKE '%;$keyword' OR `keyword` LIKE '$keyword') AND	`status` =1 AND `publish_datetime` >= $day ORDER BY `publish_Datetime` DESC LIMIT $total");
			$list2 = $results->result_array();
			foreach($list2 as $v){
				$list1[] = $v;
			}
		}
		
		foreach($list1 as $k => $v){
            if($v['vdo']){
                $v['vdo'] = date('Ymd',strtotime($v['datetime'])).'/'.$v['vdo'];
            }
            $img_id_list[] = $v['id'];
			$v['section'] = 'topic';
            $v['content'] = mb_substr($v['content'],0,50,'utf-8');
            $list1[$k] = $v;
        }
        // $this->SetImg($list,$img_id_list);
        return $list1;
		
	}

}