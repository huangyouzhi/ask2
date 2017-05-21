<?php

!defined('IN_ASK2') && exit('Access Denied');

class expertcontrol extends base {

    function expertcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("expert");
    }

    /* 添加举报 */

    function ondefault() {
        $navtitle = "问题专家";
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('user', ' expert=1');
        $expertlist = $_ENV['expert']->get_list(1, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "expert/default");
        $questionlist = $_ENV['expert']->get_solves(0, 15);
        include template('expert');
    }

}

?>