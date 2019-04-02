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

    public function GetDetail()
    {
        return array('data'=>array('demo'=>0));
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

    private function GetImg($newID)
    {
        
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

        
        $res = $this->db->get();
        // var_dump($this->db->last_query());
        $data = array();
        foreach($res->result_array() as $value){
            $time = strtotime($value['datetime']);
            $data[$value['rec_id']][] = array(
                'path' => date('Ymd',$time).'/'.$value['path'],
                'isCover' => (($value['type'])?1:0),
                'caption' => $value['caption'],
                'news_main_id'=>$value['news_main_id']
            );
        }
        return $data;
    }
    public function SetImg(&$data,$Imgs,$is_list = true)
    {

        if($is_list){
            if(count($Imgs)>0){
                $imglist = $this->GetImg($Imgs);
                foreach ($data as $key => $value) {
                    if(isset($imglist[$value['id']])){
                        $data[$key]['imgs'] = $imglist[$value['id']];
                    }
                }
            }
        }else{
            $img = $this->GetImg($data['id']);
            $data['imgs'] = $img;
        }
    }


    /**
    *   获取列表
    */
    public function GetList($page=0)
    {
        // $CatID = $this->GetCatID();
        // $Page = ($this->Page>0)?$this->Page-1:0;
        // $PageSize   = 100;
        // $count = $this->Get_All_News_list($CatID,$PageSize,$Page,true);
        // $list = $this->Get_All_News_list($CatID,$PageSize,$Page,false);
        
        
        // $img_id_list = array();
        // $video_id_list = array();
        // foreach($list as $k => $v){
        //     $img_id_list[] = $v['newsID'];
        //     if($v['vdo']!=''&&$v['vdo']!=0){
        //         $video_id_list[] = $v['vdo'];
        //     }
        //     $v['section'] = $this->SectionID;
        //     $v['cat'] = $CatID;
        //     $v['content'] = mb_substr($v['content'],0,50,'utf-8');
        //     $list[$k] = $v;

        // }
        // $this->SetImg($list,$img_id_list);
        // if(count($video_id_list)>0){
        //     $this->SetVideo($list,$video_id_list);
        // }
        // return array(
        //     'PageNums' =>(int)($count/$PageSize)+((($count%$PageSize)>0)?1:0),
        //     'data'  =>$list
        // );
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
            
            $this->db->select($this->SectionID.' As section , tmp.rec_id as id,content,content2,content3,newslayout as layout,headline as title,publish_datetime,video_path_1 as vdo,keyword,newstype as cat');
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
                // var_dump($this->db->last_query());
                echo json_encode($res->result_array());
            }else{
                $this->db->limit($PageSize,$Page*$PageSize);

                echo $this->db->count_all_results();
            }
        }
    }

}