<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_sitelogcontrol extends base {

    function admin_sitelogcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('sitelog');
    }

    function ondefault($message='') {
        if(empty($message)) unset($message);
        
          @$page = max(1, intval($this->get[2]));
        $pagesize = 100;
        $startindex = ($page - 1) * $pagesize;
       $loglist = $_ENV['sitelog']->get_list($startindex,$pagesize);
        $rownum = $this->db->fetch_total("site_log"," 1=1");
        $departstr = page($rownum, $pagesize, $page, "admin_sitelog/default");
       
        include template('sitelog','admin');
    }
    function ondelete(){
    	    if (isset($this->post['submit'])) {
    	
    	    	if($this->user['grouptype']!=1){
    	    		 $this->ondefault('只有网站创始人才能有权限删除日志，防止恶意后台用户操作！');
    	    	}
    	    	$starttime= strtotime($this->post['srchdatestart']);
    	    	$endtime= strtotime($this->post['srchdateend']);
    	    	$_ENV['sitelog']->delete($starttime,$endtime);
    	    	 $this->ondefault('日志刪除成功！');
    	    	
    	    	
    	    	
    	}
    	
    }
  

}
?>