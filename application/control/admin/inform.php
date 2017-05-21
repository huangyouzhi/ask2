<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_informcontrol extends base {

    function admin_informcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load("inform");
    }

    function ondefault($msg='') {
        @$page = max(1, intval($this->get[2]));
        $pagesize=$this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $informlist = $_ENV['inform']->get_list($startindex,$pagesize);
        $informnum=$this->db->fetch_total('inform');
        $departstr=page($informnum, $pagesize, $page,"admin_inform/default");
        $msg && $message=$msg;
        include template('informlist','admin');
    }


    function onremove() {
        if(isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['inform']->remove_by_id($qids);
            $message='举报删除成功删除！';
            unset($this->get);
            $this->ondefault($message);
        }

    }
}
?>