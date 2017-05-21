<?php

error_reporting(0);

$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('IN_ASK2', TRUE);
define('ASK2_ROOT', dirname(__FILE__));
define('ASK2_APP_ROOT', dirname(__FILE__).'/application/');
define('ASK2_STATIC_ROOT', dirname(__FILE__).'/static');
//加载核心处理文件，判断是否手机访问，https地址
include_once 'mobilecheck.php';


include ASK2_APP_ROOT . '/model/sowenda.class.php';
$sowenda = new sowenda();
$sowenda->run();
?>