<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class My_Model extends CI_Model {
	
	protected $mainDB;
	public $conn = true;
	public function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database($this->mainDB,TRUE);
		if($this->db->error()['code'] !== 0){
			$this->conn = false;
		}
	}
	

}