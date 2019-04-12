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

		$cat_id = $this->News_category_list->Mapping($section,$cat_id)['CatID'];
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
				'publish_datetime'	=>$value->publish_datetime,
				'vdo'	=> (($value->vdo==null||$value->vdo==0||$value->vdo=='')?'':$value->vdo)
			);
			$img_id_list[] = $value->id;
			if($value->vdo!=''&&$value->vdo!=0){
				$video_id_list[] = $value->vdo;
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
}