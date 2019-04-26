<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ads_view extends DefaultApi{
	
	public function __construct (){
		parent::__construct();
		$this->load->helper('url');
		
	}
	
	public function ads($id){
		$this->load->model('Ads');
		$ads = $this->Ads->select_ads($id);
		if(count($ads)>0){
			$ads = $ads[0];
			$ads_imgs = $this->Ads->select_ads_imgs($id);
			$data = array(
				
				"ads"=>$ads,
				"ads_imgs"=>$ads_imgs,
			);
			var_dump($ads_imgs);
			$this->load->view('ads/ads',$data);
		}
		
	}
	
	
}