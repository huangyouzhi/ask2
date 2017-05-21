<?php

!defined('IN_ASK2') && exit('Access Denied');

class tagscontrol extends base {

    function tagscontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("tag");
        $this->load("question");
         
        
    }
 function ondefault() {
      ;
       
        $page = max(1, intval($this->get[2]));
          $navtitle = '标签列表'.'_第'.$page.'页';
        
       
        $metakeywords = $navtitle;
        $metadescription ='标签列表'.'_第'.$page.'页';
        $pagesize = 100;
        $startindex = ($page - 1) * $pagesize;
       
        $rownum = $this->db->fetch_total('question_tag', " 1=1");
       
        $taglist = $_ENV['tag']->get_list($startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "tags-");
     
        	 include template('tag');
        
       
    }
    /* 前台查看公告列表 */

    function onview() {
        $navtitle = '标签搜索';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('question_tag', " 1=1 GROUP BY name");
        $notelist = $_ENV['tag']->get_list($startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "note/list");
        include template('notelist');
    }

}

?>