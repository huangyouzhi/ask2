<?php
!defined('IN_ASK2') && exit('Access Denied');
class testcontrol extends base {

    function testcontrol(& $get, & $post) {
    	exit("fff");
       
       
      array_push($this->regular, 'test/default') ;
       parent::__construct($get, $post);
    }
    
    function ondefault() {
    	
    	$testname="这是测试数据";
    	 exit($testname);
    }
    
}