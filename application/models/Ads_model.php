<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ads_model extends CI_Model  {
	
	
	public function __construct (){
		parent::__construct();
		$this->load->database('hl_app')
	}
	
	public function insert($data){
		
	}
	
	public function update($data){
		
	}
	
	public function select($id){
		$result = $this->db->query("SELECT * FROM hl_app_ads WHERE id = $id");
		return $result->result_array();
	}
	
	public function select_list($page = 1, $num_per_page =20 ){
		$page--; 
		$offset = $page * $num_per_page;
		$results = $this->db->query("SELECT * FROM hl_app_ads LIMIT $offset, $num_per_page");
		$rows = array();
		foreach ($results->result_array() as $row){
			$rows[] = $row;
		}
		return $rows;
	}
}