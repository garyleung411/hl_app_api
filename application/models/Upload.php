<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Upload extends CI_Model  {
	public $ftp_conf;

	
	public function __construct (){
		parent::__construct();
		$this->ftp_conf =  $this->config->item('img_server_config');
		
	}
	
	
	public function ftpUpload($file, $dest_path, $dest_name){
		
		
		$this->load->library('ftp');
		$this->ftp->connect($this->ftp_conf);
		
		
		$dirs= explode("/",$dest_path);
		
		foreach($dirs as $dir){
			
			if($dir!=""){
				if(!$this->ftp->changedir($dir,true))	{
					$this->ftp->mkdir($dir);
					$this->ftp->changedir($dir);
				}
			}
			
			
		}
		$this->ftp->changedir('~');
		$isUpload = $this->ftp->upload($file, $dest_path.$dest_name, 'binary', 0775);
		$this->ftp->close();
		return $isUpload;
	}
}