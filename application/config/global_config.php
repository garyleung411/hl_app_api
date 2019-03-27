<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*************************                       all page config                             *************************/ 

$config['PRODUCTION_HOST'] = array('admin.stheadline.com');
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['HTTPS'] = 'on';
}
$config['PROTOCOL'] = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';
$config['base_suffix']    = 'hl_app_api/';
$config['base_url']    = $config['PROTOCOL'] . '://'.$_SERVER['HTTP_HOST'].'/' . $config['base_suffix'];


/*
 *	PROD config
 */
if(in_array($_SERVER['SERVER_NAME'], $config['PRODUCTION_HOST']) ){ 
	define('ENV', 'PROD');
	$config['img_server_config'] = array(
		"hostname"	=>	"192.168.148.50",
		"username"	=>	"ftp_pop",
		"password"	=>	"XaqaM4f9Vn",
		"debug"		=>	false,
	);
	
	//img_path
	$config['daily_img_url'] = "http://static.stheadline.com/stheadline/";
	$config['instant_img_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
	$config['popnews_img_url'] = "http://res001.stheadline.com/vNews/";
	$config['life_img_url'] = "http://static.stheadline.com/stheadline/";
	$config['column_img_url'] = "http://static.stheadline.com/stheadline/";
	$config['ads_img_url'] = "http://static.stheadline.com/stheadline/";
	
	//vdo
	$config['popnews_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['instant_vdo_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
	
}

/*
 *	DEV config
 */
else{
	define('ENV', 'DEV');	
	$config['img_server_config'] = array(
		"hostname"	=>	"192.168.149.50",
		"username"	=>	"ftp_test",
		"password"	=>	"scuj4y34",
		"debug"		=>	true,
	);
	
	//img_path
	$config['daily_img_url'] = "http://192.168.148.107/stheadline/";
	$config['instant_img_url'] = "http://192.168.149.49/stheadline/inewsmedia/";//+
	$config['popnews_img_url'] = "http://192.168.149.49/stheadline/pop/";
	$config['life_img_url'] = "http://192.168.148.107/stheadline/";
	$config['column_img_url'] = "http://192.168.148.107/stheadline/";
	
	$config['ads_img_url'] = "http://192.168.149.49/stheadline/";
	
	//vdo
	$config['popnews_vdo_url'] = "http://dev.vod6.stheadline.com/";
	$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['instant_vdo_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
}

//ads type check
$config['allow_ads_image_type'] = array(1,2,5,-1);	//-1 mean landing photo
$config['allow_ads_title_type'] = array(3,4,5);	
$config['allow_ads_content_type'] = array(4);	


$config['app_config_path'] = 'json/app_config.json';
$config['hot_search_path'] = 'json/hotSearch.json';
$config['section_list_path'] = 'json/section_list.json';
$config['daily_list_path']	= 'json/{section}/list/{section}_list_{cat}.json';

$config['detail_path']	= 'json/{section}/detail/{page}/{id}.json';

//app_config
$app_config['img']['daily_img_url'] = 		$config['daily_img_url'];
$app_config['img']['instant_img_url'] =		$config['instant_img_url'];
$app_config['img']['popnews_img_url'] =		$config['popnews_img_url'];
$app_config['img']['life_img_url'] =		$config['life_img_url'];
$app_config['img']['column_img_url'] =		$config['column_img_url'];
$app_config['img']['ads_img_url'] =			$config['ads_img_url'];



$app_config['vdo']['popnews_vdo_url'] =		$config['popnews_vdo_url'];
$app_config['vdo']['life_vdo_url'] =		$config['life_vdo_url'];
$app_config['vdo']['instant_vdo_url'] =		$config['instant_vdo_url'];


$app_config['api']['api_detail'] =			"detail/[section]/[id]";
$app_config['api']['api_column_list'] =		"column/[columnid]";
$app_config['api']['api_list'] =			"list/[section]/[cat]/[!page]";//新闻列表
$app_config['api']['api_section_cat'] =		"section";//栏目分类列表

$app_config['api']['api_sp_search'] =		"sp_search/[section]/[id]";
$app_config['api']['api_search'] =			"search/[keyword]";
$app_config['api']['api_hot_search'] =		"hot_search/";//daily or instant only
$app_config['api']['api_interest'] =		"interest/";


$app_config['api']['api_list_ads'] = 		"ads/[section]/[cat]";
$app_config['api']['api_detail_ads'] = 		"ads/detail/";
$app_config['api']['api_columns_ads'] = 	"ads/columns/";

$config['app_config'] = $app_config;
