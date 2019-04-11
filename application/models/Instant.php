<?php
/**
*   即时新闻以及感兴趣
*
*/
class Instant extends CI_Model
{
    public $Expired = 1;
    public $Page = 1;
    public $year;
    public $maxdate;

    public function __construct()
    {
        parent::__construct();
        $this->year = date('Y');
        $this->load->model('Section');
    }

    public function GetInterestList()
    {
        $this->db = $this->load->database('instant',TRUE);
    	$this->db->select($this->SectionID.' As section , rec_id as id,content,content2,content3,keyword,newslayout as layout,news_main_id as newsID,headline as title,publish_datetime,video_path_1 as vdo');
    	$this->db->from('(SELECT * FROM `st_inews_main_'.$this->year.'` WHERE `status` =1 and `publish_datetime` >= NOW() - INTERVAL 3 MONTH )  tmp');
    	$this->db->order_by(rand(0,1), 'RANDOM');
    	$this->db->limit(100);
    	$res = $this->db->get();
        $data = $res->result_array();
        $list_id = array();
        foreach ($data as $value) {
           $list_id[] = $value['id'];
        }
        $this->SetImg($data,$list_id);
        // SetImg
    	return $data;
    }

    public function GetDetail($id)
    {
        $this->load->model('News');

        $s = $this->Section->Get_cat_list($this->SectionID);
        $cat = array();
        foreach ($s as $v) {
            $cat[]=$v->mapping_catid;
        }
        $res = $this->Get_News($cat,$id);
        // var_dump($res);
        if(count($res)>0){
            $map_cat = array_combine (array_column($s,'mapping_catid'), array_column($s,'cat_id'));
            $res[0]['section'] = $this->SectionID;
            $res[0]['cat'] = $map_cat[$res[0]['cat']];
            
            $this->SetImg($res[0],array(),false);
            
            if($res[0]['vdo']){
                $res[0]['vdo'] = date('Ymd',strtotime($res[0]['datetime'])).'/'.$res[0]['vdo'];
            }
            // $this->SetWriter($res[0]);
            $this->Set_related_news($res[0],$id);
            
            return array(
                'data'  =>$res[0]
            );
        }
    }

    /**
    *   设置SectionID
    */
    public function SetSectionId($SectionId)
    {
        $this->SectionID = $SectionId;
        return $this;
    }

    /**
    *   设置CatID
    */
    public function SetCatId($CatId)
    {
        if($this->CheckCat($CatId)){
            $this->CatId = $CatId;
        }
        return $this;
    }

    public function Page($page){
        $this->Page = $page;
        return $this;
    }

    private function GetImg($newID){
        
        $this->db = $this->load->database('instant',TRUE);
        $this->db->select('i.photo_content_for_headline as path,i.type,i.news_main_id,i.caption_content_for_headline as caption,m.rec_id,m.datetime');
        $this->db->from('st_inews_img_'.$this->year.' as i');
        $this->db->join('st_inews_main_'.$this->year.' as m','i.news_main_id = m.news_main_id');

        if(is_array($newID)&&count($newID)>0)
        {
            $this->db->where_in('m.rec_id',$newID);
                
        }else if($newID!=null&&$newID!='')
        {
            $this->db->where('m.rec_id',$newID);
        }
        
        $this->db->where_in('i.type',array(0,5));
        $this->db->where_in('i.deleted',array(0,null));

        
        $this->db->order_by('type','desc');
        $res = $this->db->get();
        // var_dump($this->db->last_query());
        $data = array();
        foreach($res->result_array() as $value){
            $time = strtotime($value['datetime']);
            $data[$value['rec_id']][] = array(
                'path' => date('Ymd',$time).'/'.$value['path'],
                'isCover' => (($value['type'])?1:0)
            );
        }
        return $data;
    }
    public function SetImg(&$data,$Imgs,$is_list = true)
    {
		if(count($Imgs)==0&&count($data)>0){
			$Imgs =array();
			foreach ($data as $value) {
				if(isset($value['id'])){
					$Imgs[] = $value['id'];
				}
			}
		}
        if($is_list){
            if(count($Imgs)>0){
                $imglist = $this->GetImg($Imgs);
                foreach ($data as $key => $value) {
                    if(isset($imglist[$value['id']])){
                        if(count($imglist[$value['id']])>3){
                            foreach ($imglist[$value['id']] as $k => $v) {
                               if($k>=3){
                                unset($imglist[$value['id']][$k]);
                               }
                            }
                        }else if(count($imglist[$value['id']])==2)
                        {
                            unset($imglist[$value['id']][1]);
                        }
                        $data[$key]['imgs'] = $imglist[$value['id']];
                    }
                }
            }
        }else{
            $img = $this->GetImg($data['id']);
			// var_dump($data['id']);exit;
			if(count($img)>0){
				$data['imgs'] = $img[$data['id']];
			}
        }
    }


    /**
    *   获取列表
    */
    public function GetList($page=0)
    {
        $MaxPage = 100;
        $CatID = $this->GetCatID();
        $Page = ($this->Page>0)?$this->Page-1:0;
        $PageSize   = 100;

        $count = $this->Get_All_News_list($CatID,$PageSize,$Page,true);

        if($count>$MaxPage){
            $count = $MaxPage;
        }

        if(($Page+1)>((int)($count/$PageSize)+((($count%$PageSize)>0)?1:0)))
        {
            $Page = 0;
        }

        $list = $this->Get_All_News_list($CatID,$PageSize,$Page,false);
        $img_id_list = array();

        
        foreach($list as $k => $v){
            if($v['vdo']){
                $v['vdo'] = date('Ymd',strtotime($v['datetime'])).'/'.$v['vdo'];
            }
            $img_id_list[] = $v['id'];
            $v['section'] = $this->SectionID;
            $v['cat'] = $this->CatId;
            $v['content'] = mb_substr($v['content'],0,50,'utf-8');
            $list[$k] = $v;

            $keyword = explode(';',$v['keyword']);
            if($keyword[0]==''){
                unset($keyword[0]);
            }
            $v['keyword'] = $keyword;

        }
        $this->SetImg($list,$img_id_list);
        return array(
            'PageNums' =>(int)($count/$PageSize)+((($count%$PageSize)>0)?1:0),
            'data'  =>$list//this->list_cast($list)
        );
    }

    public function Get_All_News_list($cat=-1,$PageSize=100,$Page=0,$count=FALSE)
    {
        
        $this->db = $this->load->database('instant',TRUE);
        if($cat){
            // if(!$this->maxdate = $this->Get_Max_Date($cat,$this->year))
            // {
            //     $this->maxdate  = $this->Get_Max_Date($cat,($this->year-1));
            // }
            // $this->year = date('Y',strtotime($this->maxdate ));
            // $this->db->select('nm.title,nm.newsID as id,nm.content,nm.content2,nm.content3,nm.publishDatetime,nm.keyword,nm.videoID,nm.createdBy,dhn.newsCat');
            
            $this->db->select($this->SectionID.' As section , datetime,tmp.rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo,keyword,newstype as cat');
            $this->db->from('(SELECT * FROM `st_inews_main_'.$this->year.'` WHERE `status` =1 and `publish_datetime` >= NOW() - INTERVAL 3 MONTH )  tmp');



            $this->db->join('st_inews as st','tmp.rec_id = st.rec_id', 'left');
            // if(is_array($cat)&&count($cat)>0)
            // {
            //     $this->db->where_in('dhn.newsCat',$cat);
                
            // }else if($cat!=null&&$cat!='')
            // {
            //     $this->db->where('dhn.newsCat',$cat);
            // }
            
            $this->db->where('st.newstype',$cat);
        
            // $this->db->where('publishDatetime >=',$this->maxdate );
            

            $this->db->order_by('tmp.publish_Datetime','desc');
            if(!$count){
            //     $this->db->select('dhn.dailyID as id, nm.title,nm.newsID as newsID,nm.content,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,dhn.newsCat');
                $this->db->limit($PageSize,$Page*$PageSize);
                $res = $this->db->get();
                return $res->result_array();
                // var_dump($this->db->last_query());
                // echo json_encode($res->result_array());
            }else{
                // $this->db->limit($PageSize,$Page*$PageSize);
                return $this->db->count_all_results();

                // echo $this->db->count_all_results();
            }
        }
    }

    /**
    *   检查cat是否属于当前栏目
    */
    private function CheckCat($cat){

        $num = $this->Section->Check_cat_list($this->SectionID,$cat);

        return ($num!=0);

    }

    /**
    *   获取Catid
    */
    private function GetCatID()
    {
        $this->load->model('News_category_list');
        return $this->News_category_list->Mapping($this->SectionID,$this->CatId)[0]->CatID;
    }

    /**
    *   獲取文章
    */
    public function Get_News($cat=-1,$id=-1)
    {
        $this->db = $this->load->database('instant',TRUE);
        if($cat&&$id){

            $this->db->select($this->SectionID.' As section , datetime,tmp.rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo,keyword,newstype as cat');
            $this->db->from('(SELECT * FROM `st_inews_main_'.$this->year.'` WHERE `status` =1 and `publish_datetime` >= NOW() - INTERVAL 3 MONTH )  tmp');



            $this->db->join('st_inews as st','tmp.rec_id = st.rec_id', 'left');

            
            if(is_array($cat)&&count($cat)>0)
            {
                $this->db->where_in('st.newstype',$cat);
                
            }else if($cat!=null&&$cat!='')
            {
                $this->db->where('st.newstypet',$cat);
            }
            
            // $this->db->where('dhn.status',1);
            $this->db->where('tmp.rec_id',(int)$id);
        
            $res = $this->db->get();
            return $res->result_array();
        }else{
            return false;
        }
    }

    /**
    *   获取相关新闻
    */
    private function Set_related_news(&$data, $id)
    {
        // $this->load->model('Cat');
        // var_dump($data);
        $this->CatId = $data['cat'];
        $catid = $this->GetCatID();
        $res = $this->Get_All_News_list($catid,5,0,false);
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
            //  $video_id_list[] = $value['vdo'];
            // }
            
            $return_data[] = array(
                'id'=>$value['id'],
                'title'=>$value['title'],
                'section'=>$this->SectionID,
                'cat'=>$data['cat'],
                'publish_datetime'=>$data['publish_datetime'],
                // 'vdo'=>$data['vdo'],

            );
            $imglist[] = $value['id'];
        }
        if(count($return_data)==5){
            unset($return_data[4]);
        }
        $this->SetImg($return_data,$imglist);

        // if(count($video_id_list)>0){
        //  $this->SetVideo($return_data,$video_id_list);
        // }
        $data['related_news'] = $return_data;
    }

	public function get_list_by_keyword($keyword){
		$total = $this->config->item('total_list_item');
		$day_before = $this->config->item('day_before');
		$day = date('Y-m-d',strtotime("today - $day_before days"));
		$year1 = date('Y',strtotime("today"));
		$year2 = date('Y',strtotime($day));
		
		
		$this->db = $this->load->database('instant',TRUE);	
		$results = $this->db->query("SELECT datetime, rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo 
		FROM `st_inews_main_$year1` 
		WHERE (`keyword` LIKE '%;$keyword;%' OR `keyword` LIKE '$keyword;%' OR `keyword` LIKE '%;$keyword' OR `keyword` LIKE '$keyword') AND `status` =1 AND `publish_datetime` >= $day ORDER BY `publish_Datetime` DESC LIMIT $total");
		
		$list1 = $results->result_array();
		$count = count($list1);
		
		if($count  < $total && $year1 !== $year2){
			$total = $total - $count;
			$results = $this->db->query("SELECT datetime, rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo 
			FROM `st_inews_main_$year2` 
			WHERE (`keyword` LIKE '%;$keyword;%' OR `keyword` LIKE '$keyword;%' OR `keyword` LIKE '%;$keyword' OR `keyword` LIKE '$keyword') AND	`status` =1 AND `publish_datetime` >= $day ORDER BY `publish_Datetime` DESC LIMIT $total");
			$list2 = $results->result_array();
			foreach($list2 as $v){
				$list1[] = $v;
			}
		}
		$img_id_list = array();
		foreach($list1 as $k => $v){
            if($v['vdo']){
                $v['vdo'] = date('Ymd',strtotime($v['datetime'])).'/'.$v['vdo'];
            }
            $img_id_list[] = $v['id'];
            $v['content'] = mb_substr($v['content'],0,50,'utf-8');
            $list1[$k] = $v;
        }
        $this->SetImg($list1,$img_id_list);
        return $list1;
		
	}
	
}