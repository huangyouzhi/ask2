<?php
!defined('IN_ASK2') && exit('Access Denied');

class topicdatacontrol extends base {
   
	var $whitelist;
    function topicdatacontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("topdata");
        $this->whitelist="";
    }
  function onpushindex(){
    	
    	$id=intval($this->get[2]);
    	$type=htmlspecialchars($this->get[3]);
    	$_ENV['topdata']->add($id,$type);
    	  cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
    	$this->message("首页顶置成功!");
    }
  function oncancelindex(){
    	
    	$id=intval($this->get[2]);
    	$type=htmlspecialchars($this->get[3]);
    	$_ENV['topdata']->remove($id,$type);
    	  cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
    	$this->message("取消首页顶置成功!");
    }
    
    
    
}