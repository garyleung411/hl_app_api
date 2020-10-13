<?php

class Popnews extends My_Model{

	public $Expired = 1;
	public $Page = 20;
	
    public function __construct()
    {
		$this->mainDB = 'popnews';
		parent::__construct();
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
		$rows = $this->config->item('total_popnews_list_item');
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
			$data[$key]['vdo'] .= '.mp4'; 
    	}

    	return $data;
    }
	
	
	private function Get_video_list($cat,$PageSize=10,$Page=0,$count=FALSE){
		
		
		$this->db->select('id,headline as title,video_path as vdo,catid as map_cat,length,cover_path as imgs,deleted,publish_datetime,headline as title');
		$this->db->from('video_news');
		if($cat!=-1){
			if(is_array($cat)){
				$this->db->where_in('catid',$cat);
			}else{
				$this->db->where('catid',$cat);
			}
		}else{
			$this->db->like('keywords','名人時事導航');
		}
		$day_before = $this->config->item('day_before');
		$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
		$this->db->where('publish_datetime >=',$day);
		$this->db->where('publish_datetime <= NOW()');
		$this->db->where('catid != ','');
		$this->db->where('deleted',0);
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
				"vid"					=> array('cover_path'=>$data[0]['imgs'],'headline'=>$data[0]['title'],'id'=>$data[0]['id'],'video_path'=>$data[0]['vdo'].'.mp4'),
				"imgs"					=> array(array(
											'path'=> $data[0]['imgs'],
											'caption'	=>$data[0]['title'],
											'isCover' => 0
											)),
				
			);
		}
	}
	
	private function Get_video($id){
		
		$this->db->select('id,video_path as vdo,catid as map_cat,length,cover_path as imgs,publish_datetime,headline as title,');
		$this->db->from('video_news');
		if(is_array($id)){
			$this->db->where_in('id',$id);
		}else{
			$this->db->where('id',$id);
		}
		
		$this->db->where('deleted',0);
		$day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
		$this->db->where('publish_datetime >= ',$day);
		$this->db->where('deleted',0);
		$res = $this->db->get();
		return $res->result_array();
		
    }
    public function Get_News_list_by_ID($id)
    {
    	// var_dump($id);
    	$data = $this->Get_video($id);
		// var_dump($data);
    	foreach ($data as $key => $value) {
    		$data[$key]['imgs'] = array(array(
    			'path'=> $value['imgs'],
    			'isCover'=> 1
    		));
			$data[$key]['vdo'] .= '.mp4'; 
    		// $data[$key]['map_cat'] = $value['cat'];
    		// unset($data[$key]['cat']);
    	}
    	
    	return $data;
    }
	
	 public function Get_frist_New($cat)
    {
		

		$this->load->model('News_category_list');

    	$map_cat = $this->News_category_list->cat2mapcat(3,$cat);
		$map_cat = ($map_cat==-1)?0:$map_cat;
		
    	//$list = $this->Get_All_News_list(1,$map_cat,0);
    	$list = $this->Get_video_list($map_cat);
		
		$imglist = array();
		$video_id_list = array();
    	$return_data = array();
		
    	foreach ($list as $key => $value) {
    		$return_data[] = array(
    			'id'=>$value['id'],
				'newsID'=>'',
    			'title'=>$value['title'],
				'vdo'=>array(
							'cover_path'=>'','headline'=>'','id'=>'','video_path'=>$value['vdo'].".mp4"
						),
    			'imgs'=>array(
							'0' => array('path'=>$value['imgs'],'isCover'=>1)
						),
    			'map_cat'=>$value['map_cat'],
				'publish_datetime'=>date('Y-m-d',strtotime($value['publish_datetime'])),
    		);
    		$imglist[] = $value['id'];
    	}
		//return $list;exit;
    	if(count($return_data)>0){
    		//$this->SetImg($return_data,$imglist);
			return $return_data[0];
		}else{
			return array();
		}
    }
}