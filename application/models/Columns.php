<?php

class Columns extends CI_Model
{

	public $Expired = 1;
	public $Page = 1;
	
	
    public function __construct(){
		
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
    public function GetList($CatID, $page = 0){

		
		$list = $this->Get_All_News_list($CatID,300,($CatID==1));
		$img_id_list = array();
		$video_id_list = array();
		foreach($list as $k => $v){
			$img_id_list[] = $v['id'];
			if($v['vdo']!=''&&$v['vdo']!=0){
				$video_id_list[] = $v['vdo'];
			}
			$v['publish_datetime'] = date('Y-m-d',strtotime($v['publish_datetime']));
			$v['content'] = mb_substr(strip_tags($v['content']),0,50,'utf-8');
			$list[$k] = $v;
		}

		$this->SetImg($list,$img_id_list);
		if(count($video_id_list)>0){
			$this->SetVideo($list,$video_id_list);
		}
		$this->SetWriters($list);
		return $list;
    }

	/**
	*	设置图片
	*/
    public function SetImg(&$data,$Imgs,$is_list = true,$max=3){
		if(count($Imgs)==0&&count($data)>0){
			$Imgs =array();
			foreach ($data as $value) {
				$Imgs[] = $value['id'];
			}
		}
		
    	if(count($Imgs)>0){
	    	$this->load->model('Img');
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
		$this->db = $this->load->database('daily',TRUE);
		$this->db->from('daily_hl_news as dhn');
		$this->db->where('dailyID',$id);
		$this->db->select('newsID');
		$res = $this->db->get();
		return isset($res->result_array()[0]['newsID'])?$res->result_array()[0]['newsID']:-1;
	}
	
	private function Get_All_News_list($cat=-1,$PageSize = -1,$new=true){
    	$this->db = $this->load->database('daily',TRUE);
		if($cat){
			$year = date('Y');//当年
			$date = date('Y-m-d');//当天
			$day_before = $this->config->item("column_day_before");
			$date_second = date('Y-m-d', strtotime("-$day_before day"));
			$year_second = date('Y',strtotime($date_second));
			if($new){
				$this->db->select('DISTINCT `dhe`.`columnistID`',false);
				$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat');
				
				$this->db->from('daily_hl_news as dhn');
				$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$year, ' inner');
				$this->db->join('news_extra_base_'.$year.' as neb','neb.newsID = nm.newsID','inner');
				$this->db->join('daily_hl_extra_'.$year.' as dhe','neb.extraID = dhe.extraID','inner');
				$this->db->join('news_writer_list as nw','dhe.columnistID = nw.columnistID','inner');
				$this->db->where('dhn.newsCat',9);
				
				// $this->db->where('nm.createdBy !=',0);
				$this->db->where('dhn.status',1);

				$this->db->where('nm.publishDatetime >=',date('Y-m-d') );
				$this->db->where('nm.publishDatetime <= NOW()');
				if($PageSize != -1){
					$this->db->limit($PageSize);
				}
				$this->db->order_by('nw.displayOrder','asc');
				$res = $this->db->get();
				return $res->result_array();

			}
			else{
				//其它
				if($year_second!=$year)
				{
					$year = array($year,$year_second);
				}
				$num = 0;
				if(is_array($year)){
					$return_data = array();
					
					foreach ($year as $value) {
						$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat');
						//layout默认1
						$this->db->from('daily_hl_news as dhn');
						$this->db->join('news_main_'.$value.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$value, ' inner');
						$this->db->where('dhn.newsCat',9);
						$this->db->where('dhn.status',1);

						$this->db->where('publishDatetime <',$date );
						$this->db->where('publishDatetime >=',$date_second);
						
						if($PageSize != -1){
							$this->db->limit($PageSize-$num);
						}
						
						$this->db->order_by('nm.publishDatetime','desc');
						$res = $this->db->get();
						$data = $res->result_array();
						$return_data = array_merge($return_data,$data);
						$num = count($data);

						if($num>=$PageSize)
						{
							break;
						}

					}
					return $return_data;

				}
				else{

					$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat');
					$this->db->from('daily_hl_news as dhn');
					$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$year, ' inner');
					$this->db->where('dhn.newsCat',9);
					// $this->db->where('nm.createdBy !=',0);
					$this->db->where('dhn.status',1);

					$this->db->where('publishDatetime <',$date );
					$this->db->where('publishDatetime >=',$date_second);

					if($PageSize != -1){
						$this->db->limit($PageSize-$num);
					}

					$this->db->order_by('nm.publishDatetime','desc');
					$res = $this->db->get();
					return $res->result_array();

				}
			}
				
		}
    }
	
	private function GetImg($id){
		$years = array(date('Y',strtotime('today')), date('Y',strtotime('today - 1 years ')));
		$imgs = array();
		$this->db = $this->load->database('daily',TRUE);
		foreach($years as $year){
			$this->db->select('img.path,info.isCover,dhn.dailyID as id,info.caption, img.class');
			$this->db->from('news_img_output_'.$year.' as img');
			$this->db->join('daily_hl_news as dhn',"dhn.newsID = img.newsID AND dhn.year = '$year'", 'inner');
			$this->db->join('news_img_info_'.$year.' as info','info.imgID = img.parentImgID', 'inner');
			if(is_array($id)&&count($id)>0)
			{
				$this->db->where_in('dhn.dailyID',$id);
					
			}else if($id!=null&&$id!='')
			{
				$this->db->where('dhn.dailyID',$id);
			}
			$this->db->where('img.path NOT LIKE ','%.psd');
			$this->db->group_start();
			$this->db->where('img.class',20);
			$this->db->or_where('img.class',14);
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
    *	获取作者
    */
    private function SetWriter(&$data){
    	$this->load->model('Writer');
    	$Writer = $this->Writer->GetWriter($data['newsID'],date('Y',strtotime($data['publish_datetime'])));
    	if(count($Writer)>0){
    		$data['writer'] = $Writer[$data['newsID']];
    	}
    }
    
    /**
    *	获取作者列表
    */
    private function SetWriters(&$data){
    	$this->load->model('Writer');
    	$newsID = array();
    	$date = array();
    	foreach ($data as $key => $value) {
    		$newsID[] = $value['newsID'];
    		$year = date('Y',strtotime($value['publish_datetime']));
    		if(!in_array($year,$date))
    		{
    			$date[] = $year;
    		}
    	}
    	$Writer = $this->Writer->GetWriter($newsID,$date);

    	foreach ($data as $k => $v) {
    		if(isset($Writer[$v['newsID']]))
    		{
    			$data[$k]['writer'] =  $Writer[$v['newsID']];
    		}else{
    			unset($data[$k]);
    		}
    	}
    }

    /**
    *	获取相关新闻
    */
	private function Set_related_news(&$data, $id){
    	$res = $this->Get_News_By_column($data['writer']['columnistID'],5,true);
    	$return_data = array();
    	if($res){//存在作者
			$imglist = array();
			foreach ($res as $key => $value) {
				if($value['id']==$data['id'])
				{
					continue;
				}
				$imglist[] = $value['id'];
				$return_data[] = array(
	    			'id'=>$value['id'],
	    			'title'=>$value['title'],
	    			
	    			'content' =>  mb_substr(strip_tags($value['content']),0,50,'utf-8'),
					'publish_datetime'=>$value['publish_datetime']
				);
			}
			
		}
    	$this->SetImg($return_data,$imglist);
    	if(count($return_data)==5){
    		unset($return_data[4]);
    	}
    	$data['related_news'] = $return_data;
    }

    public function GetDetail($id){
    	// $this->load->model('Section');
    	$this->load->model('News');

		$cat = array(9);
		
		$res = $this->Get_News($id);
		
		if(count($res)>0){
			$this->SetImg($res,array(),false);
			$res[0]['publish_datetime'] = date('Y-m-d',strtotime($res[0]['publish_datetime']));
			if($res[0]['vdo']!=''&&$res[0]['vdo']!=0){
				
				$this->SetVideo($res[0]);
			}
			$this->SetWriter($res[0]);
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
			
			$this->db->select('nm.title,dhn.dailyID as id, nm.newsID as newsID, nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,createdBy as writer');

			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID AND dhn.year = '.$year, 'inner');
			
		
			// $this->db->where('nm.createdBy !=',0);
			$this->db->where('dhn.status',1);
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			$this->db->where('nm.publishDatetime >=',$day);
			$this->db->where('nm.publishDatetime <= NOW()');
			$this->db->where('dhn.dailyID',(int)$id);
		
			$res = $this->db->get();
			return $res->result_array();
		}
		return array(); 
    }

    /**
    *	推荐文章获取
    */
    public function Get_News_list_by_ID($id)    {
		 
    	$years = array(date('Y',strtotime('today')), date('Y',strtotime('today - 1 years ')));
		$data = array();
		$this->db = $this->load->database('daily',TRUE);
		foreach($years as $year){
			
			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$year.' as nm','dhn.newsID = nm.newsID and dhn.year = '.$year, 'inner');
			if(is_array($id)&&count($id)>0)
			{
				$this->db->where_in('dhn.dailyID',$id);
				
			}else if($id!=null&&$id!='')
			{
				$this->db->where('dhn.dailyID',$id);
			}
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			$this->db->where('nm.publishDatetime >=',$day);
			$this->db->where('dhn.status',1);
			$this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.videoID as vdo');
			$res = $this->db->get();
			$data = array_merge($data, $res->result_array());
		}
		
        $list_id = array();
        $video_id_list = array();
		
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
		$this->SetWriters($data);
        return $data;
    }

    public function column($WriterId,$first=false){
    	
		$rows = $this->config->item('total_columns_list_item');
    	
    	$this->load->model('Writer');
    	$data = $this->Writer->GetWriter_by_ID($WriterId);
    	if($data){//存在作者
			
    		$list = $this->Get_News_By_column($WriterId,$rows);
    		if($list)
    		{
    			$imglist = array();
    			foreach ($list as $key => $value) {
    				$imglist[] = $value['id'];
    				$list[$key]['content'] =  mb_substr(strip_tags($value['content']),0,50,'utf-8');
    			}
				
    			
    		}
    		if(count($list)>0){
    			$this->SetImg($data,$list_id,true,1);
    		}
			$data[0]['list'] = $list;
			return $data[0];
    	}
    	return false;

    }



    private function Get_News_By_column($columnid,$page=10,$rand=false){
    	// 11
    	$this->db = $this->load->database('daily',TRUE);
    	$year = date('Y');
		$day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));
    	$this->db->select("dhn.dailyID  as id,nm.title,nm.content,nm.publishDatetime as publish_datetime,nm.status,nm.keyword,nm.videoID,nm.newsID,dhe.columnistID");
    	$this->db->from('news_main_'.$year.' as nm');
    	$this->db->join('news_extra_base_'.$year.' as neb','neb.newsID = nm.newsID');
    	$this->db->join('daily_hl_extra_'.$year.' as dhe','neb.extraID = dhe.extraID');
    	$this->db->join('daily_hl_news as dhn','dhn.newsID = nm.newsID AND dhn.year = '.$year);
		$this->db->where('nm.publishDatetime <= NOW()');
		$this->db->where('nm.publishDatetime >=',$day);
    	$this->db->where('dhe.columnistID',$columnid);
    	$this->db->where('nm.status',1);
		if($rand){
    			$this->db->order_by(rand(0,1), 'RANDOM');
	    	}
		else{
			$this->db->order_by('nm.publishDatetime','desc');
		}
		
    	$this->db->limit($page);
    	$res = $this->db->get();
    	$data = $res->result_array();
		
    	if(count($data)<$page)
    	{
    		$year = $year - 1;
    		$this->db->select("dhn.dailyID as id,nm.title,nm.content,nm.publishDatetime as publish_datetime,nm.status,nm.keyword,nm.videoID,nm.newsID,dhe.columnistID");
	    	$this->db->from('news_main_'.$year.' as nm');
	    	$this->db->join('news_extra_base_'.$year.' as neb','neb.newsID = nm.newsID');
	    	$this->db->join('daily_hl_extra_'.$year.' as dhe','neb.extraID = dhe.extraID');
	    	$this->db->join('daily_hl_news as dhn','dhn.newsID = nm.newsID AND dhn.year = '.$year);
	    	$this->db->where('dhe.columnistID',$columnid);
	    	$this->db->where('nm.status',1);
			$this->db->where('nm.publishDatetime <= NOW()');
			$this->db->where('nm.publishDatetime >=',$day);
	    	if($rand){
    			$this->db->order_by(rand(0,1), 'RANDOM');
	    	}
			else{
				$this->db->order_by('nm.publishDatetime','desc');
			}
	    	$this->db->limit($page-count($data));
	    	$res = $this->db->get();
	    	$data = array_merge($data,$res->result_array());
    	}

    	return $data;


    }
    public function Get_frist_New($writer)
    {
    	$list = $this->Get_News_By_column($writer,1);
		if($list)
		{
			$imglist = array();
			foreach ($list as $key => $value) {
				$imglist[] = $value['id'];
				$list[$key]['content'] =  mb_substr(strip_tags($value['content']),0,50,'utf-8');
			}
			
		}
		if(count($list)>0){
			$this->SetImg($list,$imglist);

			return $list[0];
		}else{

			return array();
		}
    }

}