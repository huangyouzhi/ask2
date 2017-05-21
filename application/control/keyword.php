<?php
//http://simonfenci.sinaapp.com/index.php?key=simon&wd={语句}



!defined('IN_ASK2') && exit('Access Denied');

class keywordcontrol extends base {
	

    function keywordcontrol(& $get, & $post) {
        $this->base($get, $post);
        
    }
    function ongetkeyword($keyword) {
    	$keyword=$_GET['keyword']==null ? $_POST['keyword']:$_GET['keyword'];
    	
    	exit($keyword);
    	 $url='http://simonfenci.sinaapp.com/index.php?key=simon&wd={'.$keyword.'}';
    	 
    	 $result=file_get_contents($url);
    	//return $result;
    	print_r($result);
    }
}