<?php

class Daily extends CI_Model
{

	public $Expired = 1;
	public $Page = 1;

	
    public function __construct(){
		
    	$this->load->model('Section');
    }
   
    public function Page($page){
    	$this->Page = $page;
    	return $this;
    }

    /**
    *	获取列表
    */
    public function GetList($cat, $page=0){
    	
    	$Page = ($this->Page>0)?$this->Page-1:0;
		$rows = $this->config->item('total_list_item');
		
		$list = $this->Get_All_News_list($rows, $cat, $Page,false);
		
		$img_id_list = array();
		$video_id_list = array();
		foreach($list as $k => $v){
			$img_id_list[] = $v['newsID'];
			if($v['vdo']!=''&&$v['vdo']!=0){
				$video_id_list[] = $v['vdo'];
			}
			
			$v['publish_datetime'] = date('Y-m-d', strtotime($v['publish_datetime']));
			$v['content'] = mb_substr(strip_tags($v['content']),0,50,'utf-8');
			$list[$k] = $v;

		}
		$this->SetImg($list,$img_id_list);
		if(count($video_id_list)>0){
			$this->SetVideo($list,$video_id_list);
		}
		return $list;
	}

	/**
	*	设置图片
	*/
    public function SetImg(&$data,$Imgs,$is_list = true,$max=3)
    {
		if(count($Imgs)==0&&count($data)>0){
			$Imgs =array();
			foreach ($data as $value) {
				if(!isset($value['newsID'])){
					$value['newsID'] = $this->Get_newsID_by_ID($value['id']);
				}
				$Imgs[] = $value['newsID'];
			}
		}
		
    	if(count($Imgs)>0){
	    	$this->load->model('Img');
	    	$img = $this->GetImg($Imgs);
	    	if(count($img)>0){
	    		foreach ($data as $key => $value) {
					if(!isset($value['newsID'])){
						$value['newsID'] = $this->Get_newsID_by_ID($value['id']);
					}
	    			$data[$key]['imgs'] = array();
	    			if(count($img)>0){
	    				foreach ($img as $k => $v) {
	    					if($value['newsID']==$v['newsID']){
								//
								if($is_list){
									unset($v['caption']);
								}
	    						unset($v['newsID']);
	    						$data[$key]['imgs'][] = $v;
	    						unset($img[$k]);
	    					}
							if($is_list && count($data[$key]['imgs'])==$max){
								break;
							}
	    				}
	    			}
					
	    			if($is_list&&count($data[$key]['imgs'])==2){
	    				$cover = true;
	    				foreach ($data[$key]['imgs'] as $i => $d) {
	    					if($d['isCover']==1&&$cover){
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
    public function SetVideo(&$data,$video=false){
    	if($video&&count($video)>0){
	    	$videos = $this->GetNewsVideo($video);
	    	if(count($videos)>0){
	    		foreach ($data as $key => $value) {
	    			if(count($videos)>0){
	    				foreach ($videos as $k => $v) {
	    					if($value['vdo']==$v['id']){
	    						$data[$key]['vdo'] = $v['video_path'].'.mp4';
	    						unset($videos[$k]);
	    					}
							
	    				}
	    			}else{
	    				return;
	    			}
	    		}
	    	}
	    }else if(!$video){
	    	$videos = $this->GetNewsVideo($data['vdo']);
		    if(count($videos)>0){
		    	$videos[0]['video_path'] = $videos[0]['video_path'].'.mp4';
		    	$data['vdo'] = $videos[0];
		    }
		    unset($data->videoID);
	    }
    }
	
	private function Get_newsID_by_ID($id){
		$this->db = $this->load->database('daily',TRUE);
		$this->db->from('daily_hl_news as dhn');
		$this->db->where('dailyID',$id);
		$this->db->select('newsID');
		$res = $this->db->get();
		return isset($res->result_array()[0]['newsID'])?$res->result_array()[0]['newsID']:-1;
	}
	
	private function Get_All_News_list($PageSize, $cat = -1,$Page=0,$count=false,$rand=false){
		$maxdate = null;
		$year = date('Y', strtotime('today'));
    	$this->db = $this->load->database('daily',TRUE);
		if($cat){
			if(!$maxdate = $this->Get_Max_Date($cat,$year))
			{
				$maxdate  = $this->Get_Max_Date($cat,($year-1));
			}
			$year = date('Y',strtotime($maxdate));
			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$year, 'inner');
			if(is_array($cat)&&count($cat)>0)
			{
				$this->db->where_in('dhn.newsCat',$cat);
				
			}else if($cat!=null&&$cat!='')
			{
				$this->db->where('dhn.newsCat',$cat);
			}
			
			$this->db->where('dhn.status',1);
		
			$this->db->where('publishDatetime >=',$maxdate );
			if($rand){
                $this->db->order_by(rand(0,1), 'RANDOM');
            }
	
			if(!$count){
				$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat as map_cat');
				$this->db->limit($PageSize,$Page*$PageSize);
				$this->db->order_by('nm.publishDatetime','desc');
				$res = $this->db->get();
				// var_dump($this->db->last_query());
				return $res->result_array();
			}else{
				return $this->db->count_all_results();
			}
		}
    }
	
	private function Get_Max_Date($cat,$year){
		$this->db = $this->load->database('daily',TRUE);
		$this->db->select_max('nm.publishDatetime');
			
		$this->db->from('daily_hl_news as dhn');
		$this->db->join("news_main_$year as nm",'dhn.newsID = nm.newsID and dhn.year = '.$year, 'inner');
			
		if(is_array($cat)&&count($cat)>0)
		{
			$this->db->where_in('dhn.newsCat',$cat);
			
		}else if($cat!=null&&$cat!='')
		{
			$this->db->where('dhn.newsCat',$cat);
		}
			
		$this->db->where('dhn.status',1);
			
		$res = $this->db->get();
		return ($res->result_array()[0]['publishDatetime'])?date('Y-m-d',strtotime($res->result_array()[0]['publishDatetime'])):false;
	}
	
	private function GetImg($newID){
		$years = array(date('Y',strtotime('today')), date('Y',strtotime('today - 1 years ')));
		$imgs = array();
		foreach($years as $year){
			$this->db = $this->load->database('daily',TRUE);
			$this->db->select('img.path,info.isCover,img.newsID,info.caption');
			$this->db->from('news_img_src_'.$year.' as img');
			$this->db->join('daily_hl_news as dhn',"dhn.newsID = img.newsID AND dhn.year = '$year'", 'inner');
			$this->db->join('news_img_info_'.$year.' as info','info.imgID = img.imgID', 'left');
			if(is_array($newID)&&count($newID)>0)
			{
				$this->db->where_in('img.newsID',$newID);
					
			}else if($newID!=null&&$newID!='')
			{
				$this->db->where('img.newsID',$newID);
			}
			
			$this->db->where('img.status',1);
			$res = $this->db->get();
			$imgs = array_merge($imgs, $res->result_array());
		}
		return $imgs;
	}
	
	private function GetNewsVideo($id){
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
		return $res->result_array();
	}

    /**
    *	获取相关新闻
    */
	private function Set_related_news(&$data, $id)
    {
    	// $this->load->model('Cat');
    	// var_dump($data);
    	$res = $this->Get_All_News_list(5, $data['map_cat'],0,false,true);
    	// var_dump($res);
    	$imglist = array();
		$video_id_list = array();
    	$return_data = array();
		
    	foreach ($res as $key => $value) {
    		if($value['id']==$id){
    			unset($res[$key]);
    			continue;
    		}
			// if($value['vdo']!=''&&$value['vdo']!=0){
			// 	$video_id_list[] = $value['vdo'];
			// }
			
    		$return_data[] = array(
    			'id'=>$value['id'],
    			'title'=>$value['title'],
    			
    			'map_cat'=>$value['map_cat'],
				'publish_datetime'=>date('Y-m-d',strtotime($value['publish_datetime'])),
    		);
    		$imglist[] = $value['newsID'];
    	}
    	if(count($return_data)==5){
    		unset($return_data[4]);
    	}
    	$this->SetImg($return_data,$imglist);
    	$data['related_news'] = $return_data;
    }

    public function GetDetail($id){
    	$this->load->model('News');
		$res = $this->Get_News($id);
		if(count($res)>0){
			$this->SetImg($res,array(),false);
			
			if($res[0]['vdo']!=''&&$res[0]['vdo']!=0){
				$this->SetVideo($res[0]);
			}else{
				$res[0]['vdo'] = "";
			}
			$res[0]['publish_datetime'] = date('Y-m-d', strtotime($res[0]['publish_datetime']));
			$this->Set_related_news($res[0],$id);
			return $res[0];
		}
    }

    

    /**
	*	獲取文章
	*/
    public function Get_News($id){
    	$this->db = $this->load->database('daily',TRUE);
		$this->db->select("year");
		$this->db->from("daily_hl_news");
		$this->db->where('dailyID',$id);
		$res = $this->db->get();
		$year = count($res->result_array())>0?$res->result_array()[0]['year']:1;
		if($year >= date('Y',strtotime('today - 1 years '))){
			$this->db->select('nm.title,dhn.dailyID as id, nm.newsID as newsID, nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat as map_cat');

			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID', 'inner');
			$this->db->where('dhn.status',1);
			$this->db->where('dhn.dailyID',(int)$id);
		
			$res = $this->db->get();
			
			return $res->result_array();
		}
		return array(); 
		
    }

    /**
    *	推荐文章获取
    */
    public function Get_News_list_by_ID($id){
		
		$years = array(date('Y',strtotime('today')), date('Y',strtotime('today - 1 years ')));
		$data = array();
		foreach($years as $year){
			$this->db = $this->load->database('daily',TRUE);
			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$year, 'inner');
			if(is_array($id)&&count($id)>0)
			{
				$this->db->where_in('dhn.dailyID',$id);
				
			}else if($id!=null&&$id!='')
			{
				$this->db->where('dhn.dailyID',$id);
			}
			
			$this->db->where('dhn.status',1);
			$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.videoID as vdo,dhn.newsCat as map_cat');
			$res = $this->db->get();
			$data = array_merge($data, $res->result_array());
		}
        $list_id = array();
        $video_id_list = array();
		
        foreach ($data as $key => $value) {
            $list_id[] = $value['newsID'];
            unset($data[$key]['newsID']);
			$data[$key]['publish_datetime'] = date('Y-m-d',strtotime($value['publish_datetime']));
			$data[$key]['content'] = mb_substr(strip_tags($value['content']),0,50,'utf-8');
            if($value['vdo']!=''&&$value['vdo']!=0){
				$video_id_list[] = $value['vdo'];
			}
        }
        $this->SetImg($data,$list_id,true,1);
        if(count($video_id_list)>0){	
			$this->SetVideo($data,$video_id_list);
		}

        return $data;
    }

}