<?php
!defined('IN_ASK2') && exit('Access Denied');
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "./lib/wxpay/lib/WxPay.Api.php";
require_once './lib/wxpay/lib/WxPay.Notify.php';
require_once './lib/wxpay/log.php';
//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
class notifycontrol extends base {
   
	var $whitelist;
    function aboutcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->whitelist="default";
    }
    function ondefault(){
    	   exit();
    }
    
    
    
}