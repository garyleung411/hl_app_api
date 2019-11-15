<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ads extends My_Model  {
	
	
	public function __construct (){
		$this->mainDB = 'hl_app';
		parent::__construct();
	}
	
	//根据section cat获取广告
	public function GetAds($section,$pdate){
		$this->db->from('ads_publish_list'.$this->platform.' as apl');
		$this->db->join('hl_app_ads'.$this->platform.'  as ads','ads.id = apl.ads_id');


		$this->db->where('apl.deleted',0);
		$this->db->where('ads.status',1);
		$this->db->where('apl.publish_datetime <=', $pdate);
		$this->db->where('apl.end_datetime >=', $pdate);
		$this->db->where('apl.section_cat_name',$section);
		$res = $this->db->get();
		// var_dump($this->db->last_query());
		$data = $res->result_array();
		// echo '<pre>';
		// var_dump($data);
		$ads_section = $this->config->item('ads_cat_list_pos');
		foreach($data as $k => $ads){
			if($ads['ads_type']==6){
				$data[$k]['ads_image'] = $this->select_ads_imgs($ads['id']);
			}
			
			$data[$k]['list_pos'] = strval($ads_section[$section][$ads['pos']-1]);
		}
		return  $this->ads_cast($data);
		
		
		
	}
	
	public function ads_cast($data){
		
		$return_data = array();
		foreach ($data as $value) {
			$tmp = array(
				'id'			=>$value['id'],
				'landing_type'	=>$value['landing_type'],
				'landing_url'	=>($value['landing_type']==1)?$value['landing_url']:$this->config->item('base_url').'ads_view/'.$value['id']."?platform=".array_keys($this->platform_type,$this->platform)[0],
				'title'			=>$value['title'],
				'content'		=>$value['content'],
				'cover'			=>$value['id'],
				'pos'			=>$value['pos'],
				'list_pos'		=>$value['list_pos'],
				
				'layout'		=>$value['ads_type']
			);
			if($value['ads_type']==6){
				foreach($value['ads_image'] as $k => $v){
					if($k<3){
						$tmp['image'][] = array(
							'path'	=>	$v['src'],
							"isCover" => ($v['pos']==0?1:0),
						);
					}
				} 
			}
			else{
				$tmp['image'][] = isset($value['ads_image'])?array(
					'path'	=>	$value['ads_image'],
					"isCover" => 1,
				):array();
			}
			$return_data[] = $tmp;
		}
		return $return_data;

	}

	
	public function select_ads($id){
		$result = $this->db->query("SELECT `id`, `ads_code`, `title`, `content`, `ads_image`, `ads_type`, `landing_url`, `landing_type`, `detail_title`, `detail_content`, `publish_datetime`, `end_datetime`,  `status` 
		FROM `hl_app_ads".$this->platform."` haa WHERE id = $id LIMIT 1");
		return $result->result_array();
	}
	
	
	
	public function select_ads_imgs($id){
		if($id<1||$id==0){
			return false;
		}
		$where = "WHERE `ads_id` = $id";
		$where .= " AND `status` = 1 ";
		$result = $this->db->query("SELECT `id`, `ads_id`, `src`, `caption`, `pos` FROM `ads_img".$this->platform."` $where ORDER BY pos ");
		return $result->result_array();
	}
	
	
}