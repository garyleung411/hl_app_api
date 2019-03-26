<?php
class Cat extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    function Get_cat_list($section,$cat_id,$page)
    {

		$this->load->model('News');
		$this->load->model('News_category_list');

		$cat_id = $this->News_category_list->Mapping($section,$cat_id)[0]->CatID;
		//找到旧表对应的catid

		$Page = ($page>0)?$page-1:0;
		$PageSize	= 100;
		//$this->SectionInfo = $this->Section->Get_cat_list($section);
		$count = $this->News->Get_All_New_list($cat_id,$PageSize,$Page,true);

		$list = $this->News->Get_All_New_list($cat_id,$PageSize,$Page,false);
		$data = array();
		$img_id_list = array();
		$video_id_list = array();
		foreach ($list as $value) {
			$data[] = array(
				'id'=> $value->id,
				'title'	=> $value->title,
				'content'=> mb_substr($value->content,0,50,'utf-8'),	
				'section'=> $section,
				'cat'	=> $cat_id,
				'publish_datetime'	=>$value->publishDatetime,
				'vdo'	=> (($value->videoID==null||$value->videoID==0||$value->videoID=='')?'':$value->videoID)
			);
			$img_id_list[] = $value->id;
			if($value->videoID!=''&&$value->videoID!=0){
				$video_id_list[] = $value->videoID;
			}
		}

		if(count($data)>0){
			return array(
				'PageNums'	=> (int)($count/$PageSize)+((($count%$PageSize)>0)?1:0),
				'data'	=> $this->SetVideo($this->SetImg($data,$img_id_list),$video_id_list)
			);
		}
		return false;
    }
    public function SetImg($data,$Imgs)
    {
    	// var_dump($data);
    	if(count($Imgs)>0){
	    	$this->load->model('Img');
	    	$img = $this->Img->GetImg($Imgs);
	    	if(count($img)>0){
	    		foreach ($data as $key => $value) {
	    			$data[$key]['imgs'] = array();
	    			if(count($img)>0){
	    				foreach ($img as $k => $v) {
	    					if($value['id']==$v->newsID&&count($data[$key]['imgs'])<3){
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
	    				return $data;
	    			}
	    		}
	    	}
	    }
    	return $data;
    }
    public function SetVideo($data,$video)
    {
    	if(count($video)>0){
	    	$this->load->model('video');
	    	$videos = $this->video->GetNewVideo($video);
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
	    				return $data;
	    			}
	    		}
	    	}
	    }
    	return $data;
    }
}