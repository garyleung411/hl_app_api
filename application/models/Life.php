<?php

class Life extends My_Model
{

	public $Expired = 1;
	public $Page = 1;

	
    public function __construct(){
		$this->mainDB = 'daily';
        parent::__construct();
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
		$rows = $this->config->item('total_life_list_item');
		
		if($cat=='0')
		{
			$this->load->model('section');
			$catlist = $this->section->Get_cat_list(4);
			$cat = array();
			foreach ($catlist as $value) {
				$cat[]=$value->mapping_catid;
			}
		}

		$list = $this->Get_All_News_list($rows, $cat, $Page);
		
		$img_id_list = array();
		$video_id_list = array();
		foreach($list as $k => $v){
			$img_id_list[] = $v['id'];
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
				$Imgs[] = $value['id'];
			}
		}
		
    	if(count($Imgs)>0){
	    	$img = $this->GetImg($Imgs);
	    	if(count($img)>0){
	    		foreach ($data as $key => $value) {
					$class20 = false;
	    			$data[$key]['imgs'] = array();
	    			if(count($img)>0){
	    				foreach ($img as $k => $v) {
	    					if($value['id']==$v['id']){
								//sql: order by class desc
								//第一圖係class20時，所有class14圖都不要
								if($class20&&$v['class']==14){
									unset($img[$k]);
									continue;
								}
								$class20 = ($v['class']==20);
								if($is_list){
									unset($v['caption']);
								}
								unset($v['id']);
								unset($v['class']);
	    						$data[$key]['imgs'][] = $v;
	    						unset($img[$k]);
	    					}
							if($is_list && count($data[$key]['imgs'])==$max){
								break;
							}
	    				}
	    			}
					if($is_list&&count($data[$key]['imgs'])>2){
						
						$data[$key]['imgs'][0]['path'] = str_replace('_fb.', '_app.', $data[$key]['imgs'][0]['path']);
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
		$this->db->from('hd_hl_news as hhn');
		$this->db->where('hdID',$id);
		$this->db->select('newsID');
		$res = $this->db->get();
		return isset($res->result_array()[0]['newsID'])?$res->result_array()[0]['newsID']:-1;
	}
	
	private function Get_All_News_list($PageSize, $cat = -1,$Page=0,$rand=false){
		if($cat){
			$year = date('Y');
			$this->db->from('hd_hl_news as hhn');
			$this->db->join('news_main_'.$year.' as nm','hhn.newsID = nm.newsID and hhn.year = '.$year, 'inner');
			if(is_array($cat)&&count($cat)>0)
			{
				$this->db->where_in('hhn.newsCat',$cat);
				
			}else if($cat!=null&&$cat!='')
			{
				$this->db->where('hhn.newsCat',$cat);
			}
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			$this->db->where('nm.publishDatetime >=',$day);
			$this->db->where('nm.publishDatetime <= NOW()');
			$this->db->where('hhn.status',1);
			$this->db->where('nm.status',1);
		
			if($rand){
                $this->db->order_by(rand(0,1), 'RANDOM');
            }
			
			$this->db->select('hhn.hdID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,hhn.newsCat as map_cat,newsLayout as layout');
			$ignoreIds = ['124','84','85','87','464','467'];
			$this->db->where_not_in('hhn.newsSubCat', $ignoreIds);
			$this->db->limit($PageSize);
			$this->db->order_by('nm.publishDatetime','desc');
			$this->db->order_by('hhn.hdID','desc');
			$res = $this->db->get();
			$data = $res->result_array();
			if(count($data)<$PageSize)
			{
				$year--;
				$this->db->from('hd_hl_news as hhn');
				$this->db->join('news_main_'.$year.' as nm','hhn.newsID = nm.newsID and hhn.year = '.$year, 'inner');
				if(is_array($cat)&&count($cat)>0)
				{
					$this->db->where_in('hhn.newsCat',$cat);
					
				}else if($cat!=null&&$cat!='')
				{
					$this->db->where('hhn.newsCat',$cat);
				}
				
				$day_before = $this->config->item('day_before');
				$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
				$this->db->where('nm.publishDatetime >=',$day);
				$this->db->where('nm.publishDatetime <= NOW()');
				$this->db->where('hhn.status',1);
				$this->db->where('nm.status',1);
				if($rand){
	                $this->db->order_by(rand(0,1), 'RANDOM');
	            }

	            $this->db->select('hhn.hdID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,hhn.newsCat as map_cat,newsLayout as layout');
				$this->db->limit($PageSize-count($data));
				$this->db->order_by('nm.publishDatetime','desc');
				$res = $this->db->get();
				$data = array_merge($data,$res->result_array());
			}
			return $data;
		}
    }
	
	private function Get_Max_Date($cat,$year){
		$this->db->select_max('nm.publishDatetime');
			
		$this->db->from('hd_hl_news as hhn');
		$this->db->join("news_main_$year as nm",'hhn.newsID = nm.newsID and hhn.year = '.$year, 'inner');
			
		if(is_array($cat)&&count($cat)>0)
		{
			$this->db->where_in('hhn.newsCat',$cat);
			
		}else if($cat!=null&&$cat!='')
		{
			$this->db->where('hhn.newsCat',$cat);
		}
			
		$this->db->where('hhn.status',1);
		$this->db->where('nm.status',1);
			
		$res = $this->db->get();
		return ($res->result_array()[0]['publishDatetime'])?date('Y-m-d',strtotime($res->result_array()[0]['publishDatetime'])):false;
	}
	
	private function GetImg($id){
		$years = array(date('Y',strtotime('today')), date('Y',strtotime('today - 1 years ')));
		$imgs = array();
		foreach($years as $year){	
			$this->db->select('img.path,info.isCover,hhn.hdID as id,info.caption, img.class');
			$this->db->from('news_img_output_'.$year.' as img');
			$this->db->join('hd_hl_news as hhn',"hhn.newsID = img.newsID AND hhn.year = '$year'", 'inner');
			$this->db->join('news_img_info_'.$year.' as info','info.imgID = img.parentImgID', 'inner');
			//$this->db->join('news_img_src_'.$year.' as src','src.imgID = img.parentImgID AND src.status = 1', 'inner');
			if(is_array($id)&&count($id)>0)
			{
				$this->db->where_in('hhn.hdID',$id);
					
			}else if($id!=null&&$id!='')
			{
				$this->db->where('hhn.hdID',$id);
			}
			$this->db->where('img.path NOT LIKE ','%.psd');
			$this->db->group_start();
			//$this->db->where('img.class',20);
			$this->db->where('img.class',14);
			//$this->db->or_where('img.class',14);
			$this->db->group_end();
			$this->db->where('img.status',1);
			$this->db->order_by('img.class', 'DESC');
			$this->db->order_by('info.isCover', 'DESC');
			$this->db->order_by('info.displayOrder', 'ASC');
			$res = $this->db->get();
			$imgs = array_merge($imgs, $res->result_array());
		}
		return $imgs;
	}
	
	private function GetNewsVideo($id){
		$popdb = $this->load->database('popnews',TRUE);
		$popdb->select('id,headline,video_path,cover_path');
		
		$popdb->from('video_news');
		
		if(is_array($id)&&count($id)>=1){
            $popdb->where_in('id',$id);
        }else{
            $popdb->where('id',$id);
        }
		$popdb->where('deleted',0);
		$res = $popdb->get();
		return $res->result_array();
	}

    /**
    *	获取相关新闻
    */
	private function Set_related_news(&$data, $id)
    {

    	$res = $this->Get_All_News_list(5, $data['map_cat'],0,true);

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
				'newsID'=>$value['newsID'],
    			'title'=>$value['title'],
    			
    			'map_cat'=>$value['map_cat'],
				'publish_datetime'=>date('Y-m-d',strtotime($value['publish_datetime'])),
    		);
    		$imglist[] = $value['id'];
    	}
    	if(count($return_data)==5){
    		unset($return_data[4]);
    	}
    	$this->SetImg($return_data,$imglist);
    	$data['related_news'] = $return_data;
    }

    public function GetDetail($id){
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
		$this->db->select("year");
		$this->db->from("hd_hl_news");
		$this->db->where('hdID',$id);
		$res = $this->db->get();
		$year = count($res->result_array())>0?$res->result_array()[0]['year']:1;
		if($year >= date('Y',strtotime('today - 1 years '))){
			$this->db->select('nm.title,hhn.hdID as id, nm.newsID as newsID, nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,hhn.newsCat as map_cat,newsLayout as layout');

			$this->db->from('hd_hl_news as hhn');
			$this->db->join('news_main_'.$year.' as nm','hhn.newsID = nm.newsID', 'inner');
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			$this->db->where('nm.publishDatetime >=',$day);
			$this->db->where('nm.publishDatetime <= NOW()');
			$this->db->where('hhn.status',1);
			$this->db->where('nm.status',1);
			$this->db->where('hhn.hdID',(int)$id);
		
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
			$this->db->from('hd_hl_news as hhn');
			$this->db->join('news_main_'.$year.' as nm','hhn.newsID = nm.newsID and hhn.year = '.$year, 'inner');
			if(is_array($id)&&count($id)>0)
			{
				$this->db->where_in('hhn.hdID',$id);
				
			}else if($id!=null&&$id!='')
			{
				$this->db->where('hhn.hdID',$id);
			}
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			$this->db->where('nm.publishDatetime >=',$day);
			$this->db->where('nm.publishDatetime <= NOW()');
			$this->db->where('hhn.status',1);
			$this->db->where('nm.status',1);
			$this->db->select('hhn.hdID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.videoID as vdo,hhn.newsCat as map_cat');
			$res = $this->db->get();
			$data = array_merge($data, $res->result_array());
		}
        $list_id = array();
        $video_id_list = array();
		//return $this->db->last_query();exit;
        foreach ($data as $key => $value) {
            $list_id[] = $value['id'];
            // unset($data[$key]['newsID']);
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

    public function Get_frist_New($cat)
    {

		$this->load->model('News_category_list');

    	$map_cat = $this->News_category_list->cat2mapcat(4,$cat);
		$map_cat = ($map_cat==-1)?0:$map_cat;

    	$list = $this->Get_All_News_list(1,$map_cat,0);

		$imglist = array();
		$video_id_list = array();
    	$return_data = array();
		
    	foreach ($list as $key => $value) {
    		$return_data[] = array(
    			'id'=>$value['id'],
				'newsID'=>$value['newsID'],
    			'title'=>$value['title'],
    			
    			'map_cat'=>$value['map_cat'],
				'publish_datetime'=>date('Y-m-d',strtotime($value['publish_datetime'])),
    		);
    		$imglist[] = $value['id'];
    	}

    	if(count($return_data)>0){
    		$this->SetImg($return_data,$imglist);
			return $return_data[0];
		}else{
			return array();
		}
    }

}
