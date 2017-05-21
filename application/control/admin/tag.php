<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_tagcontrol extends base {

    function admin_tagcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('tag');
        
    }

    function ondefault($msg = '') {
        $msg && $message = $msg;
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $taglist = $_ENV['tag']->get_list($startindex, $pagesize);
        $rownum = $_ENV['tag']->rownum();
        $departstr = page($rownum, $pagesize, $page, "admin_tag/default");
        include template('taglist', 'admin');
    }
   
    function ondelete() {
        $msg = '';
        if (isset($this->post['delete'])) {
            $_ENV['tag']->remove_by_name($this->post['delete']);
            $message = '标签刪除成功！';
        }
        $this->ondefault($message);
    }

}

?>