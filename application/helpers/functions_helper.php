<?php

/*
* FOR WORDS HANDLE
*/
function html_to_utf8 ($data)
{
	return preg_replace("/\\&\\#([0-9]{3,10})\\;/e", '_html_to_utf8("\\1")', $data);
}

function str2ncr($str, $charset="utf-8") {
	//First Make sure all words are in Normal form
	$str = html_to_utf8($str);	//convert existing NCR to normal form
	
	////// Convert the  string word(s) to NCR(&#xxxx;) //////
	$str = str_replace("'", "´", $str);
	$str = str_replace("\"", "˝", $str);
	$str = preg_replace ( '/([0-9 ])/ie' , '"&#" . ord("$1") . ";"' , $str );	
    $ncr_str = mb_convert_encoding($str, 'HTML-ENTITIES', $charset);
	$ncr_str = special_word_convert($ncr_str);
    return $ncr_str;
}

// Convert the  NCR(&#xxxx;) back to string word(s), convert NCR back to UTF-8 // 
function ncr2str($ncr_str, $charset="utf-8"){

    $decoded_str = mb_convert_encoding($ncr_str, $charset, 'HTML-ENTITIES'); 
    return $decoded_str;
}
//  Convert the word that cannot display in mobile  // 
function special_word_convert($string){
	$string = str_replace("&#59532;", "&#22487;", $string);	//埗
	$string = str_replace("&#58483;", "&#37032;", $string);	//村
	$string = str_replace("&#59263;", "&#33079;", $string);	//(月利)
	$string = str_replace("&#60632;", "&#21946;", $string);	//喺
	$string = str_replace("&#60577;", "&#36366;", $string);	//踎
	$string = str_replace("&#60634;", "&#21874;", $string);	//啲
	$string = str_replace("&#60670;", "&#32950;", $string);	//肶
	$string = str_replace("&#60625;", "&#22021;", $string);	//嘅
	$string = str_replace("&#60633;", "&#21655;", $string);	//咗
	$string = str_replace("&#60593;", "&#22050;", $string);	//嘢
	$string = str_replace("&#60652;", "&#25596;", $string);	//揼
	$string = str_replace("&#60659;", "&#20890;", $string);	//冚
	$string = str_replace("&#60622;", "&#21526;", $string);	//吖
	$string = str_replace("&#60615;", "&#36445;", $string);	//蹝
	$string = str_replace("&#60676;", "&#79;&#38746;", $string);	//O靚
	$string = str_replace("&#60696;", "&#79;&#28155;", $string);	//O添
	$string = str_replace("&#60795;", "&#79;&#28155;", $string);	//O添
	$string = str_replace("&#60730;", "&#22899;&#20110;", $string);	//女于
	$string = str_replace("&#60732;", "&#22682;", $string);	//墚
	$string = str_replace("&#60740;", "&#25164;&#31105;", $string);	//扌禁
	$string = str_replace("&#60769;", "&#23280;", $string);	//嫰
	$string = str_replace("&#60789;", "&#22303;&#29577;", $string);	//(土玉)
	$string = str_replace("&#60811;", "&#79;&#20381;", $string);	//O依
	$string = str_replace("&#60567;", "&#38910;", $string);	//馨
	$string = str_replace("&#60575;", "&#79;&#33290;", $string);	//O舊
	$string = str_replace("&#60578;", "&#23405;", $string);	//孭
	$string = str_replace("&#61088;", "&#21034;", $string);	//刪
	$string = str_replace("&#60579;", "&#28458;", $string);	//漪
	$string = str_replace("&#60582;", "&#25296;", $string);	//拐
	$string = str_replace("&#60838;", "&#29673;", $string);	//珩
	$string = str_replace("&#60585;", "&#25904;", $string);	//攰
	$string = str_replace("&#60586;", "&#22061;", $string);	//嘭
	$string = str_replace("&#60588;", "&#21530;", $string);	//吚
	$string = str_replace("&#60594;", "&#22046;", $string);	//嘞
	$string = str_replace("&#60610;", "&#30649;", $string);	//瞹
	$string = str_replace("&#60612;", "&#21554;", $string);	//吲
	$string = str_replace("&#60620;", "&#31074;", $string);	//祢
	$string = str_replace("&#60624;", "&#79;&#26550;", $string);	//O架
	$string = str_replace("&#60626;", "&#22001;", $string);	//嗱
	$string = str_replace("&#60627;", "&#26353;", $string);	//曱
	$string = str_replace("&#60630;", "&#30004;", $string);	//甴
	$string = str_replace("&#60628;", "&#36554;&#31435;", $string);	//車立
	$string = str_replace("&#60631;", "&#79;&#20491;", $string);	//O個
	$string = str_replace("&#60635;", "&#79;&#27705;", $string);	//O氹
	$string = str_replace("&#60636;", "&#79;&#34915;", $string);	//O衣
	$string = str_replace("&#60129;", "&#79;&#21038;", $string);	//O刮
	$string = str_replace("&#60641;", "&#79;&#23478;", $string);	//O家
	$string = str_replace("&#60897;", "&#79;&#20322;", $string);	//O佢
	$string = str_replace("&#60901;", "&#23626;", $string);	//届
	$string = str_replace("&#60903;", "&#20717;", $string);	//僭
	$string = str_replace("&#60648;", "&#21652;", $string);	//咔
	$string = str_replace("&#60138;", "&#79;&#38534;", $string);	//O隆
	$string = str_replace("&#60654;", "&#25598;", $string);	//揾
	$string = str_replace("&#59887;", "&#79;&#25199;", $string);	//O扯
	$string = str_replace("&#60655;", "&#21865;", $string);	//啩
	$string = str_replace("&#60656;", "&#25164;&#35910;", $string);	//扌豆
	$string = str_replace("&#60403;", "&#27705;", $string);	//氹
	$string = str_replace("&#60148;", "&#79;&#36249;", $string);	//O趙
	$string = str_replace("&#60661;", "&#79;&#24215;", $string);	//O店
	$string = str_replace("&#60917;", "&#79;&#31572;", $string);	//O答
	$string = str_replace("&#60662;", "&#20903;", $string);	//冧
	$string = str_replace("&#60407;", "&#34850;", $string);	//蠢
	$string = str_replace("&#60664;", "&#21790;", $string);	//唞
	$string = str_replace("&#60153;", "&#79;&#25773;", $string);	//O播
	$string = str_replace("&#60665;", "&#21779;", $string);	//唓
	$string = str_replace("&#60667;", "&#36397;", $string);	//踭
	$string = str_replace("&#59914;", "&#35930;", $string);	//豚
	$string = str_replace("&#59403;", "&#39224;", $string);	//餸
	$string = str_replace("&#58899;", "&#23084;", $string);	//娬
	$string = str_replace("&#58647;", "&#39938;", $string);	//鰂
	$string = str_replace("&#58400;", "&#30611;", $string);	//瞓
	$string = str_replace("&#59423;", "&#39227;", $string);	//餻
	$string = str_replace("&#60451;", "&#27028;", $string);	//榔
	$string = str_replace("&#59940;", "&#27018;", $string);	//榊
	$string = str_replace("&#60453;", "&#21410;", $string);	//厢
	$string = str_replace("&#60714;", "&#79;&#28369;", $string);	//O滑
	$string = str_replace("&#59695;", "&#33669;", $string);	//莅
	$string = str_replace("&#62530;", "&#30446;&#21450;", $string);	//目及
	return $string;
}

//  remove br tag  // 
function spanTitle($title){
    $nextTitle = preg_replace("/<br\W*?\/>/", "", $title);
    $titleStr = $nextTitle;

    return $titleStr;
}

//  remove tags  // 
function removeTags($str){
    return strip_tags(str_replace("<br />","",$str));
}


function getClientIP(){
	$ip = null;
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip= $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function echobr ($str){
	echo $str . '<br>';
}

function echopre ($strs){
	echo '<pre>';
	print_r($strs);
	echo '</pre>';
}

function getPostVal ($key){
	return isset($_POST[$key])?$_POST[$key]:null;
}

function getGetVal ($key){
	return isset($_GET[$key])?$_GET[$key]:null;
}

function getRequestVal ($key){
	return isset($_REQUEST[$key])?$_REQUEST[$key]:null;
}

function random_str($type = 'alphanum', $length = 8)
{
    switch($type)
    {
        case 'basic'    : return mt_rand();
            break;
        case 'alpha'    :
        case 'alphanum' :
        case 'num'      :
        case 'nozero'   :
                $seedings             = array();
                $seedings['alpha']    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $seedings['alphanum'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $seedings['num']      = '0123456789';
                $seedings['nozero']   = '123456789';
                
                $pool = $seedings[$type];
                
                $str = '';
                for ($i=0; $i < $length; $i++)
                {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
            break;
        case 'unique'   :
        case 'md5'      :
                    return md5(uniqid(mt_rand()));
            break;
    }
}

/*
* END FOR WORDS HANDLE
*/

/*
* FOR HITRATE STAR
*/
function get_hitrate1($__vid,$ew_cat, $hitrate_list){
    if($ew_cat == true){
        $type = 2;
    }else{
        $type = 1;
    }
    
    $hitrate_listAry = json_decode($hitrate_list, true);
    if(count($hitrate_listAry)>0)
    {
        if (!empty($hitrate_list) && json_str_matcher('display_type', $hitrate_list) !==false) {
            $tempDisplayType    = json_str_explorer('display_type', $hitrate_list);
        }
        switch ($tempDisplayType){
            case "g":
                if (!empty($hitrate_list) && json_str_matcher($type, $hitrate_list) !==false) {
                    $E___vid = intval($__vid);
                    if(isset($hitrate_listAry[$type][$E___vid])){
                        $returnResult        =  $hitrate_listAry[$type][$E___vid];
                    }else{
                        return "g1";    
                    }
                    
                }
                else
                {
                
                    return "g1";    
                }
                break;
            case "h":
            default:
                if (!empty($hitrate_list) && $hitrate_listAry[$type] !==false) {
                    $returnResult        =  $hitrate_listAry[$type][$E___vid];
                    if ($returnResult > 0){
                        return $returnResult;
                    }else{
                        return 1;
                    }
                }
                else
                {
                    return 1;    
                }
                break;
        }
        
        return $returnResult;
    }
}

function make_hitrate_html($__count="g1"){
    $htmlstr = '';
    $circle_on = '<i class="fa fa-circle"></i>';
    $circle_off = '<i class="fa fa-circle-o"></i>';
    switch($__count){
        case "g1":     $htmlstr .= $circle_on.$circle_off.$circle_off.$circle_off.$circle_off;break;
        case "g2":    $htmlstr .= $circle_on.$circle_on.$circle_off.$circle_off.$circle_off;break;
        case "g3":    $htmlstr .= $circle_on.$circle_on.$circle_on.$circle_off.$circle_off;break;
        case "g4":    $htmlstr .= $circle_on.$circle_on.$circle_on.$circle_on.$circle_off;break;
        case "g5":    $htmlstr .= $circle_on.$circle_on.$circle_on.$circle_on.$circle_on;break;
        default:
            $htmlstr .= $circle_on.$circle_off.$circle_off.$circle_off.$circle_off;break;
    }
    return $htmlstr;
}
/*
* END FOR HITRATE STAR
*/


/*
* FOR RELATIVE NEWS (get relative news by id)
*/ 
function json_str_array_explorer($key, $str)
{
    $tempProcessString    = substr($str, strpos($str, '"'.$key.'":['));
    $tempProcessString    = substr($tempProcessString, 0, strpos($tempProcessString, ']') + 1);
    $tempProcessString    = '{'.$tempProcessString.'}';
    $returnResult = json_decode($tempProcessString, true);
    return $returnResult;
}

function json_str_array_matcher($key, $str, $withQuote = true)
{
    if ($withQuote)
        return @strpos($str, '"'.$key.'":[');
    else
        return @strpos($str, $key);
}
/*
* END FOR RELATIVE NEWS
*/


/*
* FOR CONTENT NEWS
*/  
function json_str_matcher($key, $str, $withQuote = true)
{
    if ($withQuote)
        return @strpos($str, '"'.$key.'"');
    else
        return @strpos($str, $key);
}

function json_str_explorer($key, $str, $keyWithQuote = true, $valWithQuote = true)
{
    if ($keyWithQuote)
    {
        if ($valWithQuote)
        {
            $tempProcessString    = substr($str, strpos($str, '"'.$key.'"'), 40);
            $tempProcessString    = substr($tempProcessString, strpos($tempProcessString, ':') + 2, 20);
            $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, '"'));
            return $returnResult;
        }
        else
        {
            $tempProcessString    = substr($str, strpos($str, '"'.$key.'"'), 40);
            $tempProcessString    = substr($tempProcessString, strpos($tempProcessString, ':') + 1, 20);
            if (strpos($tempProcessString, ',') !== false)
                $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, ','));
            else
                $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, '}'));
            return $returnResult;
        }
    }
    else
    {
        if ($valWithQuote)
        {
            $tempProcessString    = substr($str, strpos($str, $key), 40);
            $tempProcessString    = substr($tempProcessString, strpos($tempProcessString, ':') + 2, 20);
            $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, '"'));
            return $returnResult;
        }
        else
        {
            $tempProcessString    = substr($str, strpos($str, $key), 40);
            $tempProcessString    = substr($tempProcessString, strpos($tempProcessString, ':') + 1, 20);
            if (strpos($tempProcessString, ',') !== false)
                $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, ','));
            else
                $returnResult        = substr($tempProcessString, 0, strpos($tempProcessString, '}'));
            return $returnResult;
        }
    }
}
/*
* END FOR CONTENT NEWS
*/ 

function isMobile() {
	return preg_match(
		"/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
		, $_SERVER["HTTP_USER_AGENT"]
	);
}

function clearExpiryCache($times){
	$target = APPPATH.'cache/';
	if(is_dir($target)){
		$files = glob( $target . '*.cache', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
		foreach( $files as $file )
		{
			
			if(filemtime($file)+$times <= time())
				unlink($file);
			// $cache_expires = $this->output->get_path_cache_expiration($file);
			// if ($cache_expires > 0)
			// {
				// delete_files( $file );
			// }
		}
	} elseif(is_file($target)) {
		unlink( $target );
	}
}

function log_msg($file, $text){
	$dest = dirname( APPPATH).'/pop_logs/'.$file;
	$text = $text . "\r\n";
	$fp = fopen($dest, 'a');
	fwrite($fp, $text);
	fclose($fp);
	
}
	


?>