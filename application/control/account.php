<?php
!defined('IN_ASK2') && exit('Access Denied');

class accountcontrol extends base {
   
	var $whitelist;
    function accountcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->whitelist="default,bind,bindregister";
    }
    function ondefault(){
    
    	  exit("亲，你进错地方了");
    }
    
    function onbind(){
    		$useragent = $_SERVER['HTTP_USER_AGENT']; 
    		if(!strstr($useragent, 'MicroMessenger')){
    			exit("只能微信里绑定哟，您就别费劲想耍花招了");
    		}
    		
    		$openid=$this->get[2];
    		 $getone =   $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user where openid='$openid' limit 0,1");
    	    
    		 if($getone!=null){
    	      	exit("您已经绑定账号了");
    	      }
    	    
    		 include template('bindaccount');
    }
    function onbindregister(){
    $useragent = $_SERVER['HTTP_USER_AGENT']; 
    		if(!strstr($useragent, 'MicroMessenger')){
    			exit("只能微信里绑定哟，您就别费劲想耍花招了");
    		}
       $openid=$this->get[2];
    		 $getone =   $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user where openid='$openid' limit 0,1");
    	      if($getone!=null){
    	      	exit("您已经绑定账号了");
    	      }
    	      
    	       include template('bindaccountreg');
    }
    
    
}