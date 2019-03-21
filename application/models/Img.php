<?php
/**
*	圖片
*
*/
class Img extends CI_Model
{
	public $tablename = 'news_img_src_2019';
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('daily',TRUE);
    }
	public function GetImg($newID)
	{
		$this->db->select('img.path,info.isCover');
		$this->db->from($this->tablename.' as img');
		$this->db->join('news_img_info_2019 as info','info.imgID = img.imgID', 'left');
		if(is_array($newID)&&count($newID)>0)
		{
			$this->db->where_in('img.newsID',$newID);
				
		}else if($newID!=null&&$newID!='')
		{
			$this->db->where('img.newsID',$newID);
		}
		
		$this->db->where('img.status',1);
		
		$res = $this->db->get();
		return $res->result();
	}
}