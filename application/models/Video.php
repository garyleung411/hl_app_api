<?php
/**
*	視頻
*
*/
class Video extends CI_Model
{
	public $year;
    function __construct()
    {
		parent::__construct();
		$this->db = $this->load->database('popnews',TRUE);
	}
	public function GetNewVideo($id)
	{
		$this->db->select('id,headline,video_path,cover_path');
		
		$this->db->from('video_news');
		
		if(is_array($id)&&count($id)>=1){
            $this->db->where_in('id',$id);
        }else{
            $this->db->where('id',$id);
        }
		$this->db->where('deleted',0);
		$res = $this->db->get();
		return $res->result();
	}
}
?>