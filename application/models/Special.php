<?php

class Special extends CI_Model
{

    public function __construct(){
    	$this->db = $this->load->database('hl_app',TRUE);
	}

	 public function get_special(){
        $result = $this->db->query("SELECT `title`, `publish_datetime`, `end_datetime`, `landing_url`, `landing_type`, `status` FROM `special` WHERE `id` = 1 AND '".date('Y-m-d')."' BETWEEN `publish_datetime` AND `end_datetime` LIMIT 1");		// AND `status` = 1 
		return $result->result_array();
    }

}