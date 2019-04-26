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
    	$this->load->model('section');
		
		// 'Get_cat_list';
		if($CatID=='0'){
			$catlist = $this->section->Get_cat_list(3);
			$CatID = array();
			foreach ($catlist as $value) {
				$CatID[]=$value->mapping_catid;
			}
		}

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
		$this->db->where('video_status','1');
		$this->db->order_by('publish_datetime','desc');
		$this->db->limit($PageSize,$Page*$PageSize);
		$res = $this->db->get();
		return $res->result_array();
		
    }
	
    public function GetDetail($id){
		$data = $this->Get_video($id);
		if($data){

			return array(
				"id"					=> $data[0]['id'],
				"title"					=> $data[0]['title'],
				"map_cat"					=> $data[0]['map_cat'],
				"publish_datetime"		=> $data[0]['publish_datetime'],
				"vdo"					=> array('cover_path'=>$data[0]['imgs'],'headline'=>$data[0]['title'],'id'=>$data[0]['id'],'video_path'=>$data[0]['vdo'].'.mp4'),
				"imgs"					=> array(array(
											'path'=> $data[0]['imgs'],
											'caption'	=>$data[0]['title'],
											'isCover' => 0
											)),
				
			);
		}
	}
	
	private function Get_video($id){
		$day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
    	$this->db = $this->load->database('popnews',TRUE);
		
		$this->db->select('id,video_path as vdo,catid as map_cat,length,thumb_path as imgs,publish_datetime,headline as title,');
		$this->db->from('video_news');
		if(is_array($id)){
			$this->db->where_in('id',$id);
		}else{
			$this->db->where('id',$id);
		}
		$this->db->where('deleted',0);
		$this->db->where('publish_datetime >= ',$day);
		$this->db->where('deleted',0);
		$res = $this->db->get();
		return $res->result_array();
		
    }
    public function Get_News_list_by_ID($id)
    {
    	// var_dump($id);
    	$data = $this->Get_video($id);
    	foreach ($data as $key => $value) {
    		$data[$key]['imgs'] = array(array(
    			'path'=> $value['imgs'],
    			'isCover'=> 1
    		));
    		$data[$key]['map_cat'] = $value['cat'];
    		unset($data[$key]['cat']);
    	}
    	
    	return $data;
    }
	
}