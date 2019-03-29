<?php

class Detail extends CI_Model
{
	public $Expired = 1;
	public function __construct()
    {
        parent::__construct();
        $this->Path = $this->config->item('detail_path');
    }
    public function SetSection($id)
    {
    	$this->SectionID = $id;
    	return $this;
    }
    public function SetId($id)
    {
    	$this->Id = $id;
    	return $this;
    }
    public function GetPath($section_name)
    {
    	$path = str_replace('{section}',$section_name,$this->Path);
		$page = ((int)($this->Id/1000)+1)*1000;
		$path = str_replace('{page}',$page,$path);
		$path = str_replace('{id}',$this->Id,$path);
		return $path;
    }

    public function GetData()
    {
    	$this->load->model('Section');
    	$this->load->model('News');

    	$s = $this->Section->Get_cat_list($this->SectionID);
		$cat = array();
		foreach ($s as $v) {
			$cat[]=$v->mapping_catid;
		}
		$res = $this->News->Get_New($cat,$this->Id);
		if(count($res)>0){
			$data = $this->detail_cast($res);
			return array(
				'data'	=>$data
			);
		}
		return false;

    }
    private function SetVideo(&$data)
    {
    	$this->load->model('video');
    	$videos = $this->video->GetNewVideo($data['vdo']);
    	
	    if(count($videos)>0){
	    	$videos[0]->video_path = $videos[0]->video_path.'.mp4';
	    	$data['vdo'] = $videos[0];
	    }
	    unset($data->videoID);
    }
    private function SetImg(&$data)
    {
    	$this->load->model('Img');
    	$img = $this->Img->GetImg($data['id']);

    	if(count($img)>0){
    		$data['imgs'] = $img;
    	}
	    
    }
    private function SetWriter(&$data)
    {
    	$this->load->model('Writer');
    	$Writer = $this->Writer->GetWriter($data['writer']);
    	if(count($Writer)>0){
    		$data['writer'] = $Writer[0];
    	}
    }
    private function SetAbout(&$data)
    {
    	$this->load->model('News');
    	// $this->load->model('Cat');
    	$res = $this->News->Get_All_New_list($data['cat'],5,1,false);
    	// echo '<pre>';
    	$imglist = array();
    	$return_data = array();
    	foreach ($res as $key => $value) {
    		if($value->id==$this->Id){
    			unset($res[$key]);
    			break;
    		}
    		$return_data[] = array(
    			'id'=>$value->id,
    			'title'=>$value->title,
    			'section'=>$this->SectionID,
    			'cat'=>$data['cat']
    		);
    		$imglist[] = $value->id;
    	}
    	if(count($return_data)==5){
    		unset($return_data[4]);
    	}
    	$this->SetImgs($return_data,$imglist);
    	$data['About'] = $return_data;
    }
    private function detail_cast($data){
		$return_data = array();
		$detail = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> array(),
			"section"				=> $this->SectionID,
			"cat"					=> "",
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			"writer"				=> array(),
			"layout"				=> "",
			"keyword"				=> array(),
			"topic"					=> array(),
			"about"					=> array()
		);
		
		foreach($data as $i => $d){
			$tmp = $detail;
			foreach($tmp as $k => $v){
				if($k=='content'){
					$tmp[$k] = array(
						$d->content,
						$d->content2,
						$d->content3,

					);

				}if($k=='keyword'){
					$keyword = explode(';',$d->keyword);
					if($keyword[0]==""){
						unset($keyword[0]);
					}
					$tmp[$k] = $keyword;
				}else{
					$tmp[$k] = isset($d->$k)?$d->$k:$v; 
				}

			}
			$return_data[$i] = $tmp;
		}
		$this->SetVideo($return_data[0]);
		$this->SetImg($return_data[0]);
		$this->SetAbout($return_data[0]);
		if($return_data[0]['writer']!=0&&$return_data[0]['writer']!=''){
			$this->SetWriter($return_data[0]);
		}else{
			$return_data[0]['writer']=array();
		}
		return $return_data[0];
	}
	private function SetImgs(&$data,$Imgs)
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
}