<?php

!defined('IN_ASK2') && exit('Access Denied');

class doingcontrol extends base {

    function doingcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("doing");
    }

    function ondefault() {
        $navtitle = "问答动态";

        $type = 'atentto';
        $recivetype = $this->get[2];
        if ($recivetype) {
            $type = $recivetype;
        }
        if (!$this->user['uid']) {
            $type = 'all';
        }
        $navtitletable = array(
            'all' => '问答动态',
            'my' => '我的动态',
            'atentto' => '关注的动态'
        );
        $navtitle = $navtitletable[$type];
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $doinglist = $_ENV['doing']->list_by_type($type, $this->user['uid'], $startindex, $pagesize);
        $rownum = $_ENV['doing']->rownum_by_type($type, $this->user['uid']);
        $departstr = page($rownum, $pagesize, $page, "doing/default/$type");
        if ($type == 'atentto') {
            $recommendsize = $rownum ? 3 : 6;
            $recommandusers = $_ENV['doing']->recommend_user($recommendsize);
        }
        
       // var_dump($recommandusers);
       // exit();
        include template('doing');
    }

}

?>