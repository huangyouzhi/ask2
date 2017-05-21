<?php
function ismobile() {
    $is_mobile = false;
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

function is_https()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }
  
        return FALSE;
    }
//$siteurl='https://' . $_SERVER['HTTP_HOST']  . substr($_SERVER['PHP_SELF'], 0, -9);
//if(ismobile()){
//if($_SERVER['HTTP_HOST']!='m.ask2.cn'){
//	$h_url='http://m.ask2.cn';
//	$url ='http://m.ask2.cn'.$_SERVER['REQUEST_URI'];
//	
//Header("Location:$url"); 
//exit();
//}
//	define('SITE_URL','http://m.ask2.cn' . substr($_SERVER['PHP_SELF'], 0, -9));
//	
//}else{
//	if(is_https()){
//		define('SITE_URL', 'https://www.ask2.cn'  . substr($_SERVER['PHP_SELF'], 0, -9));
//	}else{
//		define('SITE_URL', 'http://www.ask2.cn'  . substr($_SERVER['PHP_SELF'], 0, -9));
//	}
	
//}
   if(is_https()){
   	$siteurl='https://' . $_SERVER['HTTP_HOST']  . substr($_SERVER['PHP_SELF'], 0, -9);
		define('SITE_URL',$siteurl );
	}else{
		$siteurl='http://' . $_SERVER['HTTP_HOST']  . substr($_SERVER['PHP_SELF'], 0, -9);
		define('SITE_URL', $siteurl);
	}