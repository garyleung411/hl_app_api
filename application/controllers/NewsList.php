<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NewsList extends DefaultApi {

	public $SectionInfo;
	//欄目信息
	public $PageSize;
	//分頁數
	public $FilePath = './api/a';
	//文件地址
	public $config;
	//文件信息配置
	public $MaxPage;
	//最大頁數
	public $CatList;
	//分類信息
	public $data;
	//所有文章信息
	
	public function Daily()
	{
		$a = json_encode(array(
			'abc'=>array(
				'title'=>'a'
			)
		));
		//if(!file_exists($this->FilePath))
		//{
		//	mkdir($this->FilePath,0775,true);
			
		//}
		var_dump(is_dir($this->FilePath));
		//echo time()-filectime($this->FilePath.'Nav.json');
		//var_dump(date('Y-m-d H:i:s',filectime($this->FilePath.'Nav.json')));
		//$v = file_put_contents($this->FilePath.'Nav.json',$a);
		//chmod($this->FilePath.'Nav.json',0775);
		//echo md5($a);
		return;
		$this->load->model('Section');
		$this->load->model('News');
		$this->load->model('Img');
		$this->load->model('Video');
		
		$this->SectionInfo = $this->Section->Get_cat_list(2);
		//2為固定，相對日報
		$this->CatList = $this->Section->Get_cat_list($this->SectionInfo[0]->section_id);
		
		
		$data = array();
		foreach($this->CatList as $key =>$value)
		{
			$res = $this->News->Get_All_New_list($value->cat_id);
			foreach($res as $k =>$v)
			{
				$imgs = $this->Img->GetImg($v->id);
				$video = ($v->videoID)?$this->Video->GetNewVideo($v->videoID):'';
				$data[$this->SectionInfo[0]->section_id.'|'.$value->cat_id][] = array(
					'id' => $v->id,
					'title' => $v->title,
					'content' => array(
						(($v->content)?$v->content:''),(($v->content2)?$v->content2:''),(($v->content3)?$v->content3:'')
					),
					'section' => $this->SectionInfo[0]->section_id,
					'cat'	=> $value->cat_id,
					'publish_datetime'=>$v->publishDatetime,
					'imgs'=>$imgs,
					'vdo'=>$video,
					'keywords'=>$v->keyword,
					'layout'=>'',//日報為空
					'writer'=>(($v->createdBy)?$v->createdBy:''),//專欄顯示
				);
				//文件需要進行分頁
				
				
				//此處生成文件[newsID].json
				
			}
			//此處生成文件 [section]_[cat].json
		}
		
		echo json_encode($data);
	}
} 	