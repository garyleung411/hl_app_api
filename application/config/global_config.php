<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*************************                       all page config                             *************************/ 

$config['PRODUCTION_HOST'] = array('admin.stheadline.com');
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['HTTPS'] = 'on';
}
$config['PROTOCOL'] = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';
$config['base_suffix']    = '';
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
	$config['hl_app_img_url'] = "http://static.stheadline.com/stheadline/";
	
	//vdo
	$config['popnews_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['instant_vdo_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
	$config['solr'] = array(
		"project"       => "HL",
		"http_root"     => "192.168.148.105:8986/solr/",
		"debug"         => false,
		"is_cloud_mode" => true,
		"min_score"     => 0.1,
	);
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
	
	$config['hl_app_img_url'] = "http://192.168.149.49/stheadline/";
	
	//vdo
	$config['popnews_vdo_url'] = "http://dev.vod6.stheadline.com/";
	$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['instant_vdo_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
	$config['solr'] = array(
		"project"       => "HL",
		"http_root"     => "192.168.148.116:8986/solr/",
		"debug"         => false,
		"is_cloud_mode" => true,
		"min_score"     => 0.1,
	);
	if(isset(false){
		//img_path
		$config['daily_img_url'] = "http://static.stheadline.com/stheadline/";
		$config['instant_img_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
		$config['popnews_img_url'] = "http://res001.stheadline.com/vNews/";
		$config['life_img_url'] = "http://static.stheadline.com/stheadline/";
		$config['column_img_url'] = "http://static.stheadline.com/stheadline/";
		// $config['hl_app_img_url'] = "http://static.stheadline.com/stheadline/";
		
		//vdo
		$config['popnews_vdo_url'] = "http://vod6.hkheadline.com/";
		$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
		$config['instant_vdo_url'] = "http://static.stheadline.com/stheadline/inewsmedia/";
		$config['solr'] = array(
			"project"       => "HL",
			"http_root"     => "192.168.148.105:8986/solr/",
			"debug"         => false,
			"is_cloud_mode" => true,
			"min_score"     => 0.1,
		);
	}
	
}

//text releated


$config['new_line'] = array('<br>', '<br />');

$config['search_filter'] = array(
	"~","`","!","@","#","$","%","^","&","*","(",")","-","_","=","+","[","]","{","}","\\","|",";",":","'",'"',",",".","?","/","<",">",
	"～","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","－","＋","＝","｛","｝","［","］","＼","｜","；","：","＇","＂","．","／","＜","＞","，","？","｀",
	"~","·","！","@","#","￥","%","……","&","*","（","）","——","-","+","=","【","】","{","}","、","|","；","：","‘","“","”","’","《","》","，","。","？","、",
	"～","·","＠","＃","￥","％","……","＆","×","（","）","——","－","＋","＝","｛","｝","【","】","｜","＼","：","；","‘","“","”","《","》","，","、","　","＄","︿","＊","＿","＜","＞","／","［","］","‵","＂"
);

//select limit
$config['day_before'] = 90;			//For date limit
$config['column_day_before'] = 30;	//For date limit

//number_of_topic
$config['total_topic'] = 20;	
	
//number_of_list_item
$config['total_list_item'] = 100;	//topic instant
$config['total_columns_list_item'] = 11;	
$config['total_popnews_list_item'] = 20;	
$config['total_life_list_item'] = 40;	
$config['search_list_item'] = 50;	

//cache time(SEC)
$config['force_cache'] = -1;
$config['list_time'] = 600;   
$config['detail_time'] = 300;
$config['interest_time'] = 300;

//json cache path
$config['detail_path']	= 'json/{section}/detail/{page}/{id}.json';
$config['list_path']	= 'json/{section}/list/{section}_list_{cat}.json';
$config['hit_list_path'] = 'json/{section}_hit_list.json';
$config['app_config_path'] = 'json/app_config.json';
$config['hot_search_path'] = 'json/hotSearch.json';
$config['section_list_path'] = 'json/section_list.json';


$config['daily_top_list_path'] = 'json/daily-newest-top-list.json';
$config['instant_top_list_path'] = 'json/instant-newest-top-list.json';
$config['interest_list_path'] = 'json/interest/list_{page}.json';
$config['highlight_path'] = 'json/highlight.json';
$config['columns_path'] = 'json/columns/list_{id}.json';


//app_config
$app_config['img']['daily_img_url'] = 		$config['daily_img_url'];
$app_config['img']['instant_img_url'] =		$config['instant_img_url'];
$app_config['img']['popnews_img_url'] =		$config['popnews_img_url'];
$app_config['img']['life_img_url'] =		$config['life_img_url'];
$app_config['img']['column_img_url'] =		$config['column_img_url'];
$app_config['img']['hl_app_img_url'] =		$config['hl_app_img_url'];


$app_config['vdo']['popnews_vdo_url'] =		$config['popnews_vdo_url'];
$app_config['vdo']['life_vdo_url'] =		$config['life_vdo_url'];
$app_config['vdo']['instant_vdo_url'] =		$config['instant_vdo_url'];

$app_config['api']['api_highlight'] = 		"highlight";
$app_config['api']['api_detail'] =			"detail/[section]/[id]/[!cat]";
$app_config['api']['api_column_list'] =		"columns/[columnid]";
$app_config['api']['api_list'] =			"list/[section]/[cat]/[!page]";//新闻列表
$app_config['api']['api_section_cat'] =		"section/";//栏目分类列表

$app_config['api']['api_sp_search'] =		"sp_search/[section]/[id]";
$app_config['api']['api_search'] =			"search/[keyword]/[page]";
$app_config['api']['api_hot_search'] =		"hot_search/";//熱門關鍵字

$app_config['api']['api_hit_list'] =		"hit_list/[section]";//十大熱門daily or instant only
$app_config['api']['api_interest'] =		"interest";
$app_config['api']['api_special'] =			"special";


$app_config['api']['api_list_ads'] = 		"ads/[section]/[cat]";
$app_config['api']['api_detail_ads'] = 		"ads/detail";
$app_config['api']['api_columns_ads'] = 	"ads/columns";

// foreach($app_config['api'] as $k=>$v){
	// $app_config['api'][$k] = $v.'?real&gen';
// }

$config['app_config'] = $app_config;
