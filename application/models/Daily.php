<?php

class Daily extends CI_Model
{

	public $Expired = 30;
	public $Page = 1;
	public $path = '';
	public $year;
	public $maxdate;
	
    public function __construct()
    {
		
		$this->year = date('Y');
    	$this->load->model('Section');
    	$this->path = str_replace('{section}',__CLASS__,$this->config->item('daily_list_path'));
    	// $path = str_replace('{cat}',$cat,$this->config->item('daily_list_path'));
    }

    /**
    *	设置SectionID
    */
    public function SetSectionId($SectionId)
    {
		$this->SectionId = $SectionId;
		return $this;
    }
    /**
    *	设置CatID
    */
    public function SetCatId($CatId)
    {
    	if($this->CheckCat($CatId)){
			$this->CatId = $CatId;
			$this->path = str_replace('{cat}',$CatId,$this->path);
		}
		// var_dump(123);
		return $this;
    }

    public function Page($page){
    	$this->Page = $page;
    	if((int)$page>1){
	    	$this->path = str_replace('.json','_'.(int)$page.'json',$this->path);
	    }
    	return $this;
    }

    /**
    *	获取列表
    */
    public function GetListData($page=0)
    {
    	$this->load->model('News');
    	$CatID = $this->GetCatID();
    	$Page = ($this->Page>0)?$this->Page-1:0;
		$PageSize	= 100;
		$count = $this->Get_All_New_list($CatID,$PageSize,$Page,true);
		$list = $this->Get_All_New_list($CatID,$PageSize,$Page,false);
		
		
		$img_id_list = array();
		$video_id_list = array();
		foreach($list as $k => $v){
			$img_id_list[] = $v->newsID;
			$v->content = mb_substr($v->content,0,50,'utf-8');
			
			$list[$k] = json_decode(json_encode($v), True);
			if($v->vdo!=''&&$v->vdo!=0){
				$video_id_list[] = $v->vdo;
			}
		}
		$this->SetImg($list,$img_id_list);
		$this->SetVideo($list,$video_id_list);
		return array(
			'PageNums' =>(int)($count/$PageSize)+((($count%$PageSize)>0)?1:0),
			'data'	=>$list//this->list_cast($list)
		);
    }

	/**
	*	设置图片
	*/
    public function SetImg(&$data,$Imgs)
    {
    	if(count($Imgs)>0){
	    	$this->load->model('Img');
	    	$img = $this->GetImg($Imgs);
	    	if(count($img)>0){
	    		foreach ($data as $key => $value) {
					if(!isset($value['newsID'])){
						$value['newsID'] = $this->Get_newsID_by_ID($value['id']);
					}
					
					$this->year = date('Y',strtotime($value['publish_datetime'] ));
	    			$data[$key]['imgs'] = array();
	    			if(count($img)>0){
	    				foreach ($img as $k => $v) {
	    					if($value['newsID']==$v->newsID&&count($data[$key]['imgs'])<3){
	    						unset($v->newsID);
	    						$data[$key]['imgs'][] = $v;
	    						unset($img[$k]);
	    					}
	    				}
	    			}
	    			if(count($data[$key]['imgs'])==2){
	    				$cover = true;
	    				foreach ($data[$key]['imgs'] as $i => $d) {
	    					if($d->isCover==1&&$cover){
	    						$data[$key]['imgs'] = array();
	    						$data[$key]['imgs'][] = $d;
	    						$cover = false;
	    					}
	    				}
	    				if($cover){
	    					unset($data[$key]['imgs'][1]);
	    				}
	    			}
	    			if(count($img)==0){
	    				return;
	    			}
	    		}
	    	}
	    }
    }


	
    /**
	*	设置视频
	*/
    public function SetVideo(&$data,$video)
    {
    	if(count($video)>0){
	    	$videos = $this->GetNewVideo($video);
	    	if(count($videos)>0){
	    		foreach ($data as $key => $value) {
	    			if(count($videos)>0){
	    				foreach ($videos as $k => $v) {
	    					if($value['vdo']==$v->id){
	    						$data[$key]['vdo'] = $v->video_path.'.mp4';
	    						unset($videos[$k]);
	    					}
	    				}
	    			}else{
	    				return;
	    			}
	    		}
	    	}
	    }
    }
	
	private function Get_newsID_by_ID($id){
		$this->db = $this->load->database('daily',TRUE);
		$this->db->from('daily_hl_news as dhn');
		$this->db->where('dailyID',$id);
		$this->db->select('newsID');
		$res = $this->db->get();
		return $res->result_array()[0]['newsID'];
	}
	
	private function Get_All_New_list($cat=-1,$PageSize=10,$Page=0,$count=FALSE)
    {
		
    	$this->db = $this->load->database('daily',TRUE);
		if($cat){
			if(!$this->maxdate = $this->Get_Max_Date($cat,$this->year))
			{
				$this->maxdate  = $this->Get_Max_Date($cat,($this->year-1));
			}
			$this->year = date('Y',strtotime($this->maxdate ));
			// $this->db->select('nm.title,nm.newsID as id,nm.content,nm.content2,nm.content3,nm.publishDatetime,nm.keyword,nm.videoID,nm.createdBy,dhn.newsCat');
			
			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.date('Y',strtotime($this->maxdate )).' as nm','dhn.newsID = nm.newsID', 'right');
			
			if(is_array($cat)&&count($cat)>0)
			{
				$this->db->where_in('dhn.newsCat',$cat);
				
			}else if($cat!=null&&$cat!='')
			{
				$this->db->where('dhn.newsCat',$cat);
			}
			
			$this->db->where('dhn.status',1);
		
			$this->db->where('publishDatetime >=',$this->maxdate );
	
			if(!$count){
				$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat');
				$this->db->limit($PageSize,$Page*$PageSize);
				$this->db->order_by('nm.publishDatetime','desc');
				$res = $this->db->get();
				return $res->result();
			}else{
				return $this->db->count_all_results();
			}
		}
    }
	
	private function Get_Max_Date($cat,$year)
	{
		$this->db = $this->load->database('daily',TRUE);
		$this->db->select_max('nm.publishDatetime');
			
		$this->db->from('daily_hl_news as dhn');
		$this->db->join("news_main_$year as nm",'dhn.newsID = nm.newsID', 'right');
			
		if(is_array($cat)&&count($cat)>0)
		{
			$this->db->where_in('dhn.newsCat',$cat);
			
		}else if($cat!=null&&$cat!='')
		{
			$this->db->where('dhn.newsCat',$cat);
		}
			
		$this->db->where('dhn.status',1);
			
		$res = $this->db->get();
		return ($res->result()[0]->publishDatetime)?date('Y-m-d',strtotime($res->result()[0]->publishDatetime)):false;
	}
	
	private function GetImg($newID)
	{
		
		$this->db = $this->load->database('daily',TRUE);
		$this->db->select('img.path,info.isCover,img.newsID');
		$this->db->from('news_img_src_'.$this->year.' as img');
		$this->db->join('news_img_info_'.$this->year.' as info','info.imgID = img.imgID', 'left');
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
	
	private function GetNewVideo($id)
	{
		$this->db = $this->load->database('popnews',TRUE);
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
	
	 /**
    *	检查cat是否属于当前栏目
    */
    private function CheckCat($cat){

    	$num = $this->Section->Check_cat_list($this->SectionId,$cat);

    	return ($num!=0);

    }
	
	
    /**
    *	获取Catid
    */
    private function GetCatID()
    {
    	$this->load->model('News_category_list');
    	return $this->News_category_list->Mapping($this->SectionId,$this->CatId)[0]->CatID;
    }

    

	

}