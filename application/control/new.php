<?php

!defined('IN_ASK2') && exit('Access Denied');

class newcontrol extends base {
	

    function topiccontrol(& $get, & $post) {
        $this->base($get, $post);
       
    }
    function ondefault() {
    	 $this->load('question');
    	  $navtitle ="最近更新_";
    	  $seo_description=$this->setting['site_name']. '最近更新相关内容。';
             $seo_keywords= '最近更新';
    	 //回答分页      
        @$page=1;  
     @$page = max(1, intval($this->get[2]));
       $pagesize =50;// $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
      $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status('', 'all', 1); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status('', 'all', 1, $startindex, $pagesize); //问题列表数据
        $departstr = page($rownum, $pagesize, $page, "new-"); //得到分页字符串
//        
 $this->load('tag');
foreach ($questionlist as $key=>$val){
	

	   $taglist = $_ENV['tag']->get_by_qid($val['id']);
	
	$questionlist[$key]['tags']=$taglist;
        
	
}
               // $questionlist = $_ENV['question']->list_by_cfield_cvalue_status('', 0, 1,$startindex, $pagesize);
    	include template('new');
    }
    
 function onmaketag() {
    	 $this->load('question');
    	  $navtitle ="最近更新_";
    	  $seo_description=$this->setting['site_name']. '最近更新相关内容。';
             $seo_keywords= '最近更新';
    	 //回答分页      
        @$page=1;  
     @$page = max(1, intval($this->get[2]));
       $pagesize =50;// $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
      $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status('', 'all', 1); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status('', 'all', 1, $startindex, $pagesize); //问题列表数据
        $departstr = page($rownum, $pagesize, $page, "new/maketag"); //得到分页字符串
//        
 $this->load('tag');
foreach ($questionlist as $key=>$val){
	

	  
	

        $taglist=dz_segment(htmlspecialchars($val['title']));
        	$questionlist[$key]['tags']=$taglist;
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $val['id']);
	
}
              
    	include template('maketag');
    }
}