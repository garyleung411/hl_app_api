<?php
class Inews extends CI_Model
{
	public $tablename = 'st_inews_main_';
    public function __construct()
    {
        parent::__construct();
        $this->tablename = $this->tablename.date('Y');
        $this->db = $this->load->database('instant',TRUE);
    }
    public function GetList()
    {
    	$this->db->select('rec_id as id,content,content2,content3,keyword,newslayout as layout,news_main_id as newsID,headline,publish_datetime,video_path_1 as vdo');
    	$this->db->from('(SELECT * FROM `'.$this->tablename.'` WHERE `status` =1 and `publish_datetime` >= NOW() - INTERVAL 3 MONTH )  tmp');
    	$this->db->order_by(rand(0,1), 'RANDOM');
    	$this->db->limit(100);
    	$res = $this->db->get();
    	return $res->result_array();
    }
}