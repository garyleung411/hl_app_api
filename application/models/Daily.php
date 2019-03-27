<?php

class Daily extends CI_Model
{

	public $Expired = 30;
	public $Page = 1;
	public $path = '';

    public function __construct()
    {
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
    *	检查cat是否属于当前栏目
    */
    private function CheckCat($cat){

    	$num = $this->Section->Check_cat_list($this->SectionId,$cat);

    	return ($num!=0);

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
		$count = $this->News->Get_All_New_list($CatID,$PageSize,$Page,true);
		$list = $this->News->Get_All_New_list($CatID,$PageSize,$Page,false);

		return array(
			'PageNums' =>(int)($count/$PageSize)+((($count%$PageSize)>0)?1:0),
			'data'	=>$this->list_cast($list)
		);
    }

    /**
    *	获取Catid
    */
    private function GetCatID()
    {
    	$this->load->model('News_category_list');
    	return $this->News_category_list->Mapping($this->SectionId,$this->CatId)[0]->CatID;
    }

    /**
    *	转换为接口需要数据
    */
    private function list_cast($data){
		$return_data = array();
		$img_id_list = array();
		$video_id_list = array();
		$writer_id_list = array();

		$list = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> "",
			"section"				=> $this->SectionId,
			"cat"					=> $this->CatId,
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			// "layout"				=> "",
		);
		
		foreach($data as $i => $d){
			$tmp = $list;
			foreach($tmp as $k => $v){
				if($k!='content'){
					$tmp[$k] = isset($d->$k)?$d->$k:$v;
				}else{
					$tmp[$k] = isset($d->$k)?mb_substr($d->$k,0,50,'utf-8'):$v;
				}
			}

			$return_data[$i] = $tmp;
			$img_id_list[] = $d->id;
			if($d->vdo!=''&&$d->vdo!=0){
				$video_id_list[] = $d->vdo;
			}
			$writer_id_list[] = $d->writer;
		}
		$this->SetImg($return_data,$img_id_list);
		$this->SetVideo($return_data,$video_id_list);

		return $return_data;
	}

	/**
	*	设置图片
	*/
    private function SetImg(&$data,$Imgs)
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
	    				return;
	    			}
	    		}
	    	}
	    }
    }


    /**
	*	设置视频
	*/

    private function SetVideo(&$data,$video)
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
	    				return;
	    			}
	    		}
	    	}
	    }
    }

}