<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class DefaultApi extends CI_Controller {
	
	protected $Path = 'json/';//api總路徑
	public $Expired = 600;//超時時間，默認十分鐘
	
	public function __construct()
	{
		parent::__construct();
		$this->Path = $_SERVER['DOCUMENT_ROOT'].'/json/';
		$this->Makedir($this->Path);
	}
	
	/**
	*	檢查文件是否過期或是否存在
	*/
	protected function Checkfile($filepath)
	{
		if(is_file($filepath))
		{
			if($this->Expired == -1){
				return true;
			}
			$time = filemtime($filepath);
			return ((time()-$time)<$this->Expired);
		}
		return false;
	}
	
	/**
	*	文件夾檢查並創建
	*/
	protected function Makedir($Path)
	{
		if(!is_dir($Path))
		{
			return mkdir($Path,0775,true);
		}
		return true;
	}
	
	/**
	*	讀取文件內容
	*/
	protected function Getfile($filepath)
	{
		return (($this->Checkfile($filepath))?file_get_contents($filepath):false);
	}
	
	/**
	*	保存文件內容
	*/
	protected function Savefile($filepath,$data)
	{
		$this->Makedir(dirname($filepath));
		file_put_contents($filepath,$data);
		chmod($filepath,0775);
	}
	
	/**
	*	設置header並輸出數據
	*/
	protected function PushData($data)
	{
		header("Content-type:application/json");
		echo $data;
	}
	
	/**
	*	設置header並輸出數據
	*/
	protected function show_error($error_code = 0){
		$output = json_encode(array(
				'result' =>0,
				// 'error'=>$error_code,
			),JSON_UNESCAPED_SLASHES);
		$this->PushData($output);
		exit;
	}
	
	/**
	*	設置超時時間
	*/
	protected function SetExpired($time)
	{
		$this->Expired = $time;
		return $this;
	}
	
}