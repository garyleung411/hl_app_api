<?php

class Popnews extends CI_Model{

	public $Expired = 1;
	public $Page = 20;
	
    public function __construct()
    {
    	$this->load->model('Section');
    	// $this->DetailPath = $this->config->item('detail_path');
    }
   
    public function Page($page){
    	$this->Page = $page;
    	return $this;
    }

    /**
    *	获取列表
    */
    public function GetList($CatID,$page=0)
    {
		$rows = $this->config->item('total_popnews');
    	

    	$data = $this->Get_video_list($CatID,$rows,$page);

    	
    	foreach ($data as $key => $value) {
    		$data[$key]['imgs'] = array(array(
    			'caption'=> $value['title'],
    			'path'=> $value['imgs'], 
    			'isCover'=> 0
    		));
    	}

    	return $data;
    }
	
	
	private function Get_video_list($cat,$PageSize=10,$Page=0,$count=FALSE){
		
    	$this->db = $this->load->database('popnews',TRUE);
		
		$this->db->select('id,video_path as vdo,catid as map_cat,length,thumb_path as imgs,deleted,publish_datetime,headline as title');
		$this->db->from('video_news');
		if($cat!=-1){
			if(is_array($cat)){
				$this->db->where_in('catid',$cat);
			}else{
				$this->db->where('catid',$cat);
			}
		}
		$this->db->where('catid != ','');
		$this->db->order_by('publish_datetime','desc');
		$this->db->limit($PageSize,$Page*$PageSize);
		$res = $this->db->get();
		return $res->result_array();
		
    }
	
    public function GetDetail()
	{
		return array();
	}
	
}