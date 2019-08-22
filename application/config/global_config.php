<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*************************                       all page config                             *************************/ 

$config['PRODUCTION_HOST'] = array('hlapp.stheadline.com');
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
	ini_set('display_errors', 'Off');
	error_reporting(0);
	$config['img_server_config'] = array(
		"hostname"	=>	"192.168.148.50",
		"username"	=>	"ftp_pop",
		"password"	=>	"XaqaM4f9Vn",
		"debug"		=>	false,
	);
	
	//img_path
	$config['daily_img_url'] = "https://static.stheadline.com/stheadline/";
	$config['instant_img_url'] = "https://static.stheadline.com/stheadline/inewsmedia/";
	// $config['popnews_img_url'] = "http://res001.stheadline.com/vNews/";
	$config['popnews_img_url'] = "https://static.stheadline.com/stheadline/pop/";
	
	$config['life_img_url'] = "https://static.stheadline.com/stheadline/";
	$config['column_img_url'] = "https://static.stheadline.com/stheadline/";
	$config['hl_app_img_url'] = "http://203.80.1.61/stheadline/";
	
	//vdo
	$config['popnews_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['life_vdo_url'] = "http://vod6.hkheadline.com/";
	$config['instant_vdo_url'] = "https://static.stheadline.com/stheadline/inewsmedia/";
	$config['solr'] = array(
		"project"       => "appcollect",
		"http_root"     => "192.168.149.106:8983/solr/",
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
		"project"       => "appcollect",
		"http_root"     => "192.168.149.106:8983/solr/",
		"debug"         => false,
		"is_cloud_mode" => true,
		"min_score"     => 0.1,
	);
}

//text releated
$config['new_line'] = array('<br>', '<br />');

$config['search_filter'] = array(
	"~","`","!","@","#","$","%","^","&","*","(",")","-","_","=","+","[","]","{","}","\\","|",";",":","'",'"',",",".","?","/","<",">",
	"～","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","－","＋","＝","｛","｝","［","］","＼","｜","；","：","＇","＂","．","／","＜","＞","，","？","｀",
	"~","·","！","@","#","￥","%","……","&","*","（","）","——","-","+","=","【","】","{","}","、","|","；","：","‘","“","”","’","《","》","，","。","？","、",
	"～","·","＠","＃","￥","％","……","＆","×","（","）","——","－","＋","＝","｛","｝","【","】","｜","＼","：","；","‘","“","”","《","》","，","、","　","＄","︿","＊","＿","＜","＞","／","［","］","‵","＂",'「','」'
);
$config['unicode_filter'] = array(
	"\ufeff",
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
//****TEST ONLY****//
$config['list_time'] = 120;  
$config['detail_time'] = 60;
$config['interest_time'] = 60;

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

// $config['ads_cat_list_pos'] = array(
	// "index" => '首頁',	
	// "1-1" => '即時-港聞',	
	// "1-2" => '即時-娛樂',	
	// "1-3" => '即時-中國',	
	// "1-4" => '即時-國際',	
	// "1-5" => '即時-地產',	
	// "1-6" => '即時-財經',	
	// "1-7" => '即時-體育',	
	// "2-1" => '日報-港聞',
	// "2-2" => '日報-中國',
	// "2-3" => '日報-國際',
	// "2-4" => '日報-地產',
	// "2-5" => '日報-財經',
	// "2-6" => '日報-體育',
	// "2-7" => '日報-副刊',
	// "2-8" => '日報-娛樂',
	// "2-9" => '日報-馬經',
	// "4-1" => '生活-旅遊',
	// "4-2" => '生活-飲食',
	// "4-3" => '生活-影音',
	// "4-4" => '生活-駕駛',
	// "4-5" => '生活-時尚',
	// "4-6" => '生活-健康',
	// "3" => '影片',
	// "5" => '專欄',
	// "detail" => '文章內頁',		
	// "columns" => '個入專欄列表',
// );
$config['ads_cat_list_pos'] = array(
	"index" => array(2,4,7,10,13,16),	//首頁
	"1-1" => array(2,4,7,10,13,16),	//即時-港聞
	"1-2" => array(2,4,7,10,13,16),	//即時-娛樂
	"1-3" => array(2,4,7,10,13,16),	//即時-中國
	"1-4" => array(2,4,7,10,13,16),	//即時-國際
	"1-5" => array(2,4,7,10,13,16),	//即時-地產
	"1-6" => array(2,4,7,10,13,16),	//即時-財經
	"1-7" => array(2,4,7,10,13,16),	//即時-體育
	"2-1" => array(2,4,7,10,13,16),	//日報-港聞
	"2-2" => array(2,4,7,10,13,16),	//日報-中國
	"2-3" => array(2,4,7,10,13,16),	//日報-國際
	"2-4" => array(2,4,7,10,13,16),	//日報-地產
	"2-5" => array(2,4,7,10,13,16),	//日報-財經
	"2-6" => array(2,4,7,10,13,16),	//日報-體育
	"2-7" => array(2,4,7,10,13,16),	//日報-副刊
	"2-8" => array(2,4,7,10,13,16),	//日報-娛樂
	"2-9" => array(2,4,7,10,13,16),	//日報-馬經
	"4-1" => array(2,4,7,10,13,16),	//生活-旅遊
	"4-2" => array(2,4,7,10,13,16),	//生活-飲食
	"4-3" => array(2,4,7,10,13,16),	//生活-影音
	"4-4" => array(2,4,7,10,13,16),	//生活-駕駛
	"4-5" => array(2,4,7,10,13,16),	//生活-時尚
	"4-6" => array(2,4,7,10,13,16),	//生活-健康
	"3" => array(2,4,7,10,13,16),	//影片
	"5" => array(2,4,7,10,13,16),	//專欄
	"columns" => array(3),	//個入專欄列表
);
foreach($config['ads_cat_list_pos'] as $k => $v){
	if(!in_array($k,array("columns", "3","index"))){
		$config['ads_cat_list_pos']['detail-'.$k] = array(1,2,3);
	}
}
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
$app_config['api']['api_detail'] =			"detail/[section]/[id]/";
$app_config['api']['api_column_list'] =		"columns/[columnid]";
$app_config['api']['api_list'] =			"list/[section]/[cat]/";//新闻列表
$app_config['api']['api_section_cat'] =		"section/";//栏目分类列表

$app_config['api']['api_sp_search'] =		"sp_search/";
$app_config['api']['api_search'] =			"search/[keyword]/[page]";
$app_config['api']['api_hot_search'] =		"hot_search/";//熱門關鍵字

$app_config['api']['api_hit_list'] =		"hit_list/[section]";//十大熱門daily or instant only
$app_config['api']['api_interest'] =		"interest";
$app_config['api']['api_special'] =			"special";


$app_config['api']['api_list_ads'] = 		"ads/[section]/[cat]";

//error_code
require('hlapp/error_code.php');
$app_config['error'] = $error_code;
//android app version
require('hlapp/android.php');
$app_config['android'] =			$android;
//ios app version
require('hlapp/ios.php');
$app_config['ios'] =		$ios;	
$config['app_config'] = $app_config;





