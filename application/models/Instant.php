<?php
/**
*   即时新闻以及感兴趣
*
*/
class Instant extends CI_Model
{
	
    public $Expired = 1;
    public $Page = 1;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Section');
    }

    public function GetInterestList(){
		$total = 100;
        $day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
        $years = array(
			date('Y',strtotime("today")),//今年
		);

        if(!in_array(date('Y',strtotime($day)), $years)){
			$years[] = date('Y',strtotime($day));
		}
		$list = array();
		$this->db = $this->load->database('instant',TRUE);
		foreach($years as $year){
			if(count($list)<$total){	
				$this->db->select('datetime, st.rec_id as id,content,keyword,newslayout as layout,news_main_id as newsID,headline as title,publish_datetime,video_path_1 as vdo, vid, newstype as map_cat');
				$this->db->from("(SELECT * FROM `st_inews_main_$year` WHERE `status` =1 and `publish_datetime` >= '$day' and `publish_datetime` <= NOW())  tmp");
				$this->db->join('st_inews as st','tmp.rec_id = st.rec_id', 'inner');
				$this->db->order_by(rand(0,1), 'RANDOM');
				$this->db->limit($total);
				$res = $this->db->get();
				$list = array_merge($list,$res->result_array());
				
			}
			$total -= count($list);
		}
        $list_id = array();
        foreach ($list as $key => $value) {
			$list[$key]['content'] = mb_substr(strip_tags($value['content']),0,50,'utf-8');
			$list_id[] = $value['id'];
			
        }
        $this->SetImg($list,$list_id);
    	return $list;
    }

    public function GetDetail($id){
        $res = $this->Get_News($id);
        
        if(count($res)>0){ 
            $this->SetImg($res[0],array(),false);
            if($res[0]['vdo']){
                $res[0]['vdo'] = date('Ymd',strtotime($res[0]['datetime'])).'/'.$res[0]['vdo'];
            }
			if($res[0]['vid']!=''&&$res[0]['vid']>0){
				$this->SetVideo($res[0]);
			}else{
				$res[0]['vid'] = "";
			}
            $this->Set_related_news($res[0],$id,$res[0]['keyword']);
            $this->Set_relevant_news($res[0],$id);
			
            return $res[0];
           
        }
    }

    public function Page($page){
        $this->Page = $page;
        return $this;
    }

    private function GetImg($newID){
        $day_before = $this->config->item('day_before');
		$day = date('Y-m-d',strtotime("today - $day_before days"));
		 $years = array(
			date('Y',strtotime("today")),//今年
		);
        if(!in_array(date('Y',strtotime($day)), $years)){
			$years[] = date('Y',strtotime($day));
		}
		$data = array();
		$this->db = $this->load->database('instant',TRUE);
		foreach($years as $year){
			$this->db->select('i.photo_content_for_headline as path,i.type,i.news_main_id,i.caption_content_for_headline as caption,m.rec_id,m.datetime');
			$this->db->from('st_inews_img_'.$year.' as i');
			$this->db->join('st_inews_main_'.$year.' as m','i.news_main_id = m.news_main_id');

			if(is_array($newID)&&count($newID)>0)
			{
				$this->db->where_in('m.rec_id',$newID);
					
			}else if($newID!=null&&$newID!='')
			{
				$this->db->where('m.rec_id',$newID);
			}
			
			$this->db->where_in('i.type',array(0,5));
			$this->db->where_in('i.deleted',array(0,null));
			$this->db->order_by('display_order','asc');
			$this->db->order_by('type','desc');
			$res = $this->db->get();
			
			foreach($res->result_array() as $value){
				$time = strtotime($value['datetime']);
				$data[$value['rec_id']][] = array(
					'path' => date('Ymd',$time).'/'.$value['path'],
					'caption' => $value['caption'],
					'isCover' => (($value['type'])?1:0)
				);
			}
		}
        return $data;
    }
    
	public function SetImg(&$data,$Imgs,$is_list = true,$max = 3){
		
        if($is_list){
            if(count($Imgs)>0){
                $imglist = $this->GetImg($Imgs);
				
				function filterArray($value){
					return ($value['isCover'] == 1);
				}
				foreach ($data as $key => $value) {
                    if(isset($imglist[$value['id']])){
						if(count($imglist[$value['id']]) >= 3){
							$tmp= array_filter($imglist[$value['id']], 'filterArray');
							$name = $tmp[0]["path"];
							$name = str_replace('_popup.jpg','',$name);
							$name = str_replace('_popup.jpeg','',$name);
							foreach($imglist[$value['id']] as $k => $v){
								if(strpos($v['path'], $name)!== false && $v['isCover']!= 1){
									unset($imglist[$value['id']][$k]);
									continue;
								}
							}
							
							$imglist[$value['id']] = array_values($imglist[$value['id']]);
						}
					}
				}
				
                foreach ($data as $key => $value) {
                    if(isset($imglist[$value['id']])){
                        if(count($imglist[$value['id']])>$max){
                            foreach ($imglist[$value['id']] as $k => $v) {
								if($k>=$max){
									unset($imglist[$value['id']][$k]);
								}
                            }
                        }else if(count($imglist[$value['id']])==2)
                        {
                            unset($imglist[$value['id']][1]);
                        }
                        $data[$key]['imgs'] = $imglist[$value['id']];
						foreach($data[$key]['imgs'] as $k => $v){
							unset($v['caption']);
							// var_dump($data);exit;
							if(strtotime($data[$key]['datetime']) >= strtotime('2019-05-20')){
								if($v['isCover']== 1){
									$v['path'] = str_replace('_popup.jpg', '_370.jpg', $v['path']);
									$v['path'] = str_replace('_popup.jpeg', '_370.jpeg', $v['path']);
								}
								else{
									$v['path'] = str_replace('.jpg', '_370.jpg', $v['path']);
									$v['path'] = str_replace('.jpeg', '_370.jpeg', $v['path']);
									
								}
								$v['path'] = str_replace('_content_370', '_370', $v['path']);
							}
							$data[$key]['imgs'][$k] = $v;
						}

                    }
                }
            }
        }
		else{
            $img = $this->GetImg($data['id']);
			if(isset($img)&&count($img)>0){
				foreach($img[$data['id']] as $k => $v){
					if($v['isCover']==1){
						unset($img[$data['id']][$k]);
					}
					else{
						if(strtotime($data['datetime']) >= strtotime('2019-05-20')){
							$v['path'] = str_replace('.jpg', '_600.jpg', $v['path']);
							$v['path'] = str_replace('.jpeg', '_600.jpeg', $v['path']);
						}
						$v['path'] = str_replace('_content_600', '_600', $v['path']);
						$img[$data['id']][$k]['path'] = $v['path'];
					}
				}
				$img[$data['id']] = array_values($img[$data['id']]);
				$data['imgs'] = $img[$data['id']];
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
	    					if($value['vid']==$v['id']){
	    						$data[$key]['vid'] = $v['video_path'].'.mp4';
	    						unset($videos[$k]);
	    					}
							
	    				}
	    			}else{
	    				return;
	    			}
	    		}
	    	}
	    }
		else if(!$video){
	    	$videos = $this->GetNewsVideo($data['vid']);
		    if(count($videos)>0){
		    	$videos[0]['video_path'] = $videos[0]['video_path'].'.mp4';
		    	$data['vid'] = $videos[0];
		    }
		    unset($data->videoID);
	    }
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
     *   获取列表
     */
	public function GetList($cat, $page=0){
        $rows = $this->config->item('total_list_item');
        $list = $this->Get_All_News_list($rows, $cat);
        $img_id_list = array();

        
        foreach($list as $k => $v){
            if($v['vdo']){
                $v['vdo'] = date('Ymd',strtotime($v['datetime'])).'/'.$v['vdo'];
            }
            $img_id_list[] = $v['id'];
            $v['content'] = mb_substr(strip_tags($v['content']),0,50,'utf-8');
            $list[$k] = $v;
        }
        $this->SetImg($list,$img_id_list);
		return $list;
    }

	
	public function Get_All_News_list($rows, $cat = -1, $keyword=array(), $rand = false){

        $total = $rows;
        $day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
        $years = array(
			date('Y',strtotime("today")),//今年
		);

        if(!in_array(date('Y',strtotime($day)), $years)){
			$years[] = date('Y',strtotime($day));
		}
		$list = array();
		$this->db = $this->load->database('instant',TRUE);  
		foreach($years as $year){
			if(count($list)<$total){
				
				$this->db->select('datetime, main.rec_id as id,content,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo, vid,st.newstype as map_cat,keyword');
				$this->db->from('st_inews_main_'.$year.' as main');
				$this->db->join('st_inews as st','main.rec_id = st.rec_id', 'inner');
				$this->db->where('main.status',1);
				$this->db->where('publish_datetime >=',$day);
				$this->db->where('`publish_datetime` <= NOW()');
				if($cat != -1)
					$this->db->where('st.newstype',$cat);


				if(count($keyword)>0){
					 $this->db->group_start();
					foreach ($keyword as $v) {
						$this->db->or_like('keyword',$v);
					}
					$this->db->group_end();
				}

				$this->db->order_by('publish_Datetime','desc');
				if($rand){
					$this->db->order_by(rand(0,1), 'RANDOM');
				}
				$this->db->limit($total);
				$res = $this->db->get();
				// var_dump($this->db->last_query());
				$list = array_merge($list,$res->result_array());
			}
			$total -= count($list);
		}
        return $list;
    }
	

    /**
    *   獲取文章
    */
    public function Get_News($id){
        $this->db = $this->load->database('instant',TRUE);
        $day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
		$this->db->select("year");
		$this->db->from("st_inews");
		$this->db->where('rec_id',$id);
		$res = $this->db->get();
		$year = count($res->result_array())>0?$res->result_array()[0]['year']:1;
		
		if($year >= date('Y',strtotime($day))){
			$year = $res->result_array()[0]['year'];
			
			$this->db->select('datetime,tmp.rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo, vid,keyword,newstype as map_cat');
			$this->db->from("(SELECT * FROM `st_inews_main_$year` WHERE `status` =1 and `publish_datetime` >= '$day' AND `publish_datetime` <= NOW())  tmp");
			$this->db->join('st_inews as st','tmp.rec_id = st.rec_id', 'inner');
			$this->db->where('tmp.rec_id',(int)$id);
			$res = $this->db->get();
			return $res->result_array();
		}
		return array();     
    }

    /**
    *   related_news get by keyword
    */
    private function Set_related_news(&$data, $id, $keyword=''){
        // $this->load->model('Cat');
        // var_dump($data);
        $keyword = explode(';',$keyword);
        if($keyword[0]==''){
            unset($keyword[0]);
        }
        $res = $this->Get_All_News_list(5, -1, $keyword, true);
        $imglist = array();
        $video_id_list = array();
		
        $return_data = array();
        foreach ($res as $key => $value) {
            if($value['id']==$id){
                unset($res[$key]);
                continue;
            }
            $return_data[] = array(
                'id'=>$value['id'],
                'title'=>$value['title'],
				'datetime'=>$value['datetime'],
                'publish_datetime'=>$value['publish_datetime'],
				'map_cat'=>$value['map_cat'],
            );
            $imglist[] = $value['id'];
        }
        if(count($return_data)==5){
            unset($return_data[4]);
        }
        $this->SetImg($return_data,$imglist);


        $data['related_news'] = $return_data;

    }
	
	/**
    *   relevant_news get by news_main_id
    */
	private function Set_relevant_news(&$data, $id){
		$this->db = $this->load->database('instant',TRUE);
        $day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
		$this->db->select("year");
		$this->db->from("st_inews");
		$this->db->where('rec_id',$id);
		$res = $this->db->get();
		$year = count($res->result_array())>0?$res->result_array()[0]['year']:1;
		$return_data = array();
		if($year >= date('Y',strtotime($day))){
			//Get all relevant_news_main_id 
			$res = $this->db->query("SELECT re.relevant_news_year, re.relevant_news_main_id
			FROM `st_inews_main_$year` nm
			INNER JOIN st_inews_relevant_$year re ON re.news_main_id = nm.news_main_id
			WHERE nm.rec_id = $id AND re.deleted = 0");
			$result = $res->result_array();
			$news_year_list = array();
			//id spilt by year
			foreach($result as $n){
				if(!array_key_exists($n['relevant_news_year'], $news_year_list)){
					$news_year_list[$n['relevant_news_year']] = array();
				}
				$news_year_list[$n['relevant_news_year']][] = $n['relevant_news_main_id'];
			}
			
			$day_before = $this->config->item('day_before');
			$day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
			foreach(array_keys($news_year_list) as $y){
				$id_list = $news_year_list[$y];
				$this->db->select('nm.rec_id as id,headline as title,newstype as map_cat');
				$this->db->from("`st_inews_main_$y` nm");
				$this->db->join('st_inews as st','nm.rec_id = st.rec_id', 'inner');
				$this->db->where_in('nm.news_main_id', $id_list);
				$this->db->where('`nm`.`status` =1 and `nm`.`publish_datetime` >= '$day' AND `nm`.`publish_datetime` <= NOW()');
				$res = $this->db->get();
				$result = $res->result_array();
				foreach($result as $n){
					$return_data[] = $n;
				}
			}
			
		}
        // var_dump($return_data);exit;
		$data['relevant_news'] = $return_data;
    }

    /**
    *   推荐文章获取
    */
    public function Get_News_list_by_ID($id){
		
		$day_before = $this->config->item('day_before');
		$day = date('Y-m-d',strtotime("today - $day_before days"));
		$years = array(date('Y',strtotime("today")),date('Y',strtotime("today - 1 years ")));
		$data = array();
        $this->db = $this->load->database('instant',TRUE);
		foreach($years as $year){
			$this->db->select(' datetime,tmp.rec_id as id,content,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo, vid,newstype as map_cat');
			$this->db->from("(SELECT * FROM `st_inews_main_$year` WHERE `status` =1 and `publish_datetime` >= '$day' AND `publish_datetime` <= NOW())  tmp");

			$this->db->join('st_inews as st','tmp.rec_id = st.rec_id', 'inner');
			
			$this->db->where_in('tmp.rec_id',$id);

			$res = $this->db->get();

			
			$data = array_merge($data,$res->result_array());
		}
        $list_id = array();
        foreach ($data as $key => $value) {
           $list_id[] = $value['id'];
		   $data[$key]['content'] = mb_substr(strip_tags($value['content']),0,50,'utf-8');
            if($value['vdo']!=''){
                // var_dump($value['vdo']);
                $data[$key]['vdo'] = date('Ymd',strtotime($value['datetime'])).'/'.$value['vdo'];
            }
        }
		
        $this->SetImg($data,$list_id,true,1);
        return $data;
    }

	public function get_list_by_keyword($keyword){
		$total = $this->config->item('total_list_item');
        $day_before = $this->config->item('day_before');
        $day = date('Y-m-d',strtotime("today - $day_before days"));//90天前的日期
        $years = array(
			date('Y',strtotime("today")),//今年
		);

        if(!in_array(date('Y',strtotime($day)), $years)){
			$years[] = date('Y',strtotime($day));
		}
		$list = array();
		$this->db = $this->load->database('instant',TRUE);	
		foreach($years as $year){
			if(count($list)<$total){
				$results = $this->db->query("SELECT datetime, nm.rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo, vid, newstype as map_cat  
					FROM `st_inews_main_$year` nm
					INNER JOIN st_inews st ON st.rec_id = nm.rec_id
					WHERE (`keyword` LIKE '%;$keyword;%' OR `keyword` LIKE '$keyword;%' OR `keyword` LIKE '%;$keyword' OR `keyword` LIKE '$keyword') AND nm.`status` =1 AND `publish_datetime` >= '$day' AND `publish_datetime` <= NOW() ORDER BY `publish_Datetime` DESC LIMIT $total");
		
				$list = array_merge($list,$results->result_array());
			}
			$total -= count($list);
		}
		
		$img_id_list = array();
		foreach($list as $k => $v){
            if($v['vdo']){
                $v['vdo'] = date('Ymd',strtotime($v['datetime'])).'/'.$v['vdo'];
            }
            $img_id_list[] = $v['id'];
            $v['content'] = mb_substr(strip_tags($v['content']),0,50,'utf-8');
            $list[$k] = $v;
        }
        $this->SetImg($list,$img_id_list);
        return $list;
		
	}
	
}