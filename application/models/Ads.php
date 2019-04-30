<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ads extends CI_Model  {
	

	public function __construct (){
		parent::__construct();
		$this->load->database('hl_app');
	}
	
	public function select_ads($id){
		$result = $this->db->query("SELECT `id`, `ads_code`, `title`, `content`, `ads_image`, `ads_type`, `landing_url`, `landing_type`, `detail_title`, `detail_content`, `publish_datetime`, `end_datetime`,  `status` 
		FROM `hl_app_ads` haa WHERE id = $id LIMIT 1");
		return $result->result_array();
	}
	
	
	
	public function select_ads_imgs($id){
		if($id<1||$id==0){
			return false;
		}
		$where = "WHERE `ads_id` = $id";
		$where .= " AND `status` = 1 ";
		$result = $this->db->query("SELECT `id`, `ads_id`, `src`, `caption`, `pos` FROM `ads_img` $where ORDER BY pos ");
		return $result->result_array();
	}
	
	
}