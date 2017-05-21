<?php

!defined('IN_ASK2') && exit('Access Denied');

class indexcontrol extends base {
var $whitelist;
    function indexcontrol(& $get, & $post) {
        $this->base($get, $post);
          $this->whitelist="notfound";
        
    }

    
    function ondefault() {
    	$this->load('setting');
         
    	// if(!is_mobile()){
    	 	$tnosolvelist=$this->fromcache('nosolvelist');
    	// }else{
    	 	//$nosolvelist=$this->fromcache('nosolvelist');
    	// }
    		
    	 $topiclist=$this->fromcache('topiclist');
    	// if(!is_mobile()){
    	 	 $nosolvelist=array_merge($tnosolvelist,$topiclist);
    	$nosolvelist=$this->getnewlist_bytime($nosolvelist);
    	// }
    	

//    	foreach ($nosolvelist as $key=>$val){
//    		
//    		echo $val['title'].'----'.$val['format_time']."---".$val['sortime']."<br>";
//    	}
//
//exit();
        /* SEO */
        $this->setting['seo_index_title'] && $seo_title = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_index_title']);
        $this->setting['seo_index_description'] && $seo_description = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_index_description']);
        $this->setting['seo_index_keywords'] && $seo_keywords = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_index_keywords']);
        $navtitle = $this->setting['site_alias'];

    	$this->load('topic');
    	$art_rownum=$_ENV['topic']->rownum_by_user_article();
    	$userarticle=$_ENV['topic']->get_user_articles(0,5);

    
  	include template('index');
  

 
    
    }
  
    function getnewlist_bytime($arr){
    	
    	$i=0;
    	$len=count($arr);
    	$j=0;
    	$d=0;
    	for($i;$i<$len;$i++){
    		for($j=0;$j<$len;$j++){
    		   if ($arr[$i]['sortime'] > $arr[$j]['sortime']) {
                $d = $arr[$j];
                $arr[$j] = $arr[$i];
                $arr[$i] = $d;
            }
    		}
    	}
    	return $arr;
//    	//定义一个空数组
//    	$temparr=array();
//    	//定义一个值，假定这个值是最大的
//    	$maxtime=0;
//    	$i=0;
//    foreach ($arr as $key=>$val){
//	
//    	//如果数组等于空就假定第一个值是最大的
//    	if($temparr==null){
//    		//$val['sortime']=strtotime($val['format_time']);
//    		$maxtime=$val['sortime'];
//    		array_push($temparr, $val);
//    	}else{
//    	//如果下一个值比当前值小就插入数组屁股后面，如果比当前值大就插入数组开始位置
//    	//$val['sortime']=strtotime($val['format_time']);
//	    	if($maxtime>$val['sortime']){
//	    		array_push($temparr, $val);
//	    	}else{
//	    		//如果当前值比最初值大就重新赋值
//	    		$maxtime=$val['sortime'];
//	    		$tmp_maxarr=array();
//	    		array_push($tmp_maxarr, $val);
//	    	  array_splice($temparr,$i-1,0,$tmp_maxarr);
//	    	}
//    	}
//    	$i++;
//    	
//      }
//    	return $temparr;
    }

    function onhelp() {
       $this->message("即将跳转网站教程中...","cat-219");
    }

    function ondoing() {
        include template("doing");
    }
     function onnotfound(){
     	  include template("404");
     }
    /* 查询图片是否需要点击放大 */

    function onajaxchkimg() {
        list($width, $height, $type, $attr) = getimagesize($this->post['imgsrc']);
        ($width > 300) && exit('1');
        exit('-1');
    }

    function ononline() {
        $navtitle = "当前在线";
        $this->load('user');
        @$page = max(1, intval($this->get[2]));
        $pagesize = 30;
        $startindex = ($page - 1) * $pagesize;
        $onlinelist = $_ENV['user']->list_online_user($startindex, $pagesize);
        $onlinetotal = $_ENV['user']->rownum_onlineuser();
        $departstr = page($onlinetotal, $pagesize, $page, "index/online");
        include template("online");
    }

}

?>