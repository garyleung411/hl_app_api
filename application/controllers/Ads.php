<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ads extends CI_Controller{
	
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
	}
	
	public function ads_list($page = 1){
		$data = array(
			"page"=>$page,
			
		);
		$this->load->view('ads/List',$data);
	}
	
	public function form($id = -1){
		$method = 'add';
		if($id!=-1){
			$method = 'edit';
			$ads = '';
			
		}
		$data = array(
			"action"		=>	$method,
			"ads_img_url"	=>	$this->config->item("ads_img_url"),
		);
		$this->load->view('ads/Form',$data);
	}
	
	private function is_empty(){
		$filed_cname = array(
			//basic
			'ads_code'			=> '識別碼',
			'ads_type' 			=> '廣告類型',
			'landing_type'		=> '鏈接類型',
			'publish_datetime'	=> '出版日期',
			'end_datetime'		=> '完結日期',
			//list
			'title'				=> '標題',
			'content'			=> '内容',
			'ads_image'			=> '圖片',
			//detail
			'detail_title'		=> '文章標題',
			'detail_content'	=> '文章内容',
			'landing_url'		=> '外部URL',
		); 
		
		$data = array();
		$tmp = array();
		
		//check basic data
		$tmp['ads_code'] = getPostVal('ads_code');
		$tmp['ads_type'] = getPostVal('ads_type');
		$tmp['landing_type'] = getPostVal('landing_type');
		$tmp['publish_datetime'] = getPostVal('publish_datetime');
		$tmp['end_datetime'] = getPostVal('end_datetime');
		$missing = array();
		foreach($tmp as $k => $v){
			if($v = null || empty($v)){
				$missing[] = $filed_cname[$k];
			}
			$data[$k] = $tmp[$k];
		}
		if(count($missing)>0){	
			
			return $missing;
		}
		
		//check list data
		$tmp = array();
		if(in_array($data['ads_type'], $this->config->item('allow_ads_image_type'))){
			$tmp['ads_image'] = getPostVal('ads_image');
		}
		if(in_array($data['ads_type'], $this->config->item('allow_ads_title_type'))){
			$tmp['title'] = getPostVal('title');
		}
		if(in_array($data['ads_type'], $this->config->item('allow_ads_content_type'))){
			$tmp['content'] = getPostVal('content');
		}
		foreach($tmp as $k => $v){
			if($v = null || empty($v)){
				$missing[] = $filed_cname[$k];
			}
			$data[$k] = $tmp[$k];
		}
		if(count($missing)>0){	
			return $missing;
		}
		
		//check detail data
		$tmp = array();
		if($data['landing_type']==1){
			$tmp['landing_url'] = getPostVal('landing_url');
		}
		else{
			$tmp['detail_title'] = getPostVal('detail_title');
			$tmp['detail_content'] = getPostVal('detail_content');
		}
		foreach($tmp as $k => $v){
			if($v = null || empty($v)){
				$missing[] = $filed_cname[$k];
			}
			$data[$k] = $tmp[$k];
		}
		if(count($missing)>0){	
			return $missing;
		}
		$data[0] = true;
		return $data;
	}
	
	public function add(){
		$data = $this->is_empty();
		if($data[0] !== true){
			echo '<script>alert("'.'請輸入'.implode(', ', $data).'的資料!!!'.'");window.history.back();</script>';
			exit;
		}
		unset($data[0]);
		$pub_day =strtotime($data["publish_datetime"]); 
		$end_day =strtotime($data["end_datetime"]);
		if( $pub_day > $end_day ){
			echo '<script>alert("'.'錯誤的出版日子!!!'.'");window.history.back();</script>';
			exit;
		}
		if(isset($data['landing_url'])&&!filter_var($data['landing_url'], FILTER_VALIDATE_URL)){
			echo '<script>alert("'.'錯誤的外部URL!!!'.'");window.history.back();</script>';
			exit;
		}
		
		
		
	}
	
	public function edit($id){
		
	}
	
	public function uploadImage($ads_type){
		$allow_ads_type = $this->config->item('allow_ads_image_type');
		$allow_size = 2*1024*1024 ;
		$allow_ext = array('png','jpg','jpeg');	//,'gif','bmp'
		$allow_weight = null;
		$allow_height = null;
		
		if(in_array($ads_type, $allow_ads_type )){
			$file = $_FILES['image']['tmp_name'];
			$size = $_FILES['image']['size'];
			$ext = strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
			
			list($width, $height) = getimagesize($file);
			
			//validation
			if($size>$allow_size){
				echo json_encode(array(
					'code'=>501,
					'msg'=>'檔案大小超過'. ($allow_size/1024/1024) .'MB!',
				));
				return;
			}
			if(!in_array($ext, $allow_ext)){
				echo json_encode(array(
					'code'=>502,
					'msg'=>'圖片只能為'.implode(',', $allow_ext).'格式!',
				));
				return;
			}
			if($ads_type==1){
				$allow_weight =300;
				$allow_height =100;
			}
			if($ads_type==3){
				$allow_weight =300;
				$allow_height =300;
			}
			// if($allow_weight != null && $width != $allow_weight ){
				// echo json_encode(array(
					// 'code'=>503,
					// 'msg'=>'圖片大小只能為'.$allow_weight.'x'.$allow_height.'!',
				// ));
				// return;
			// }
			// if($allow_height != null && $height != $allow_height ){
				// echo json_encode(array(
					// 'code'=>503,
					// 'msg'=>'圖片大小只能為'.$allow_weight.'x'.$allow_height.'!',
				// ));
				// return;
			// }
			
			
			
			//upload
			$upload_path = 'hl_app_ads/'.date('Y').'/'.date('m').'/'.date('d').'/';
			$upload_file = date('Ymdhis').'_'.rand(1000,9999).'.'.$ext;
			$this->load->model('Upload');
			if($this->Upload->ftpUpload($file, $upload_path, $upload_file)){
				echo json_encode(array(
					'code'=>200,
					'file'=>$upload_path.$upload_file,
					'msg'=>'添加成功'
				));
				return;
			}
			else{
				
				echo json_encode(array(
					'code'=>504,
					'msg'=>'添上載失敗',
				));
				return;
			}
			
			
		}
		else{
			echo json_encode(array(
					'code'=>505,
					'msg'=>'此廣告類型不能上載圖片',
			));
			return;
		}
	}
    
	
}