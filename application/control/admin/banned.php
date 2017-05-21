<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_bannedcontrol extends base {

    function admin_bannedcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("banned");
    }

    function onadd($msg = '') {
        if (isset($this->post['submit'])) {
            $_ENV['banned']->add($this->post['ip'], $this->post['expiration']);
            $message = "IP添加成功!";
        }
        $iplist = $_ENV['banned']->get_list();
        $msg && $message = $msg;
        include template("addbanned", "admin");
    }

    function onremove() {
        if (isset($this->post['id'])) {
            $_ENV['banned']->remove($this->post['id']);
        }
        $this->onadd("IP地址删除成功");
    }

}

?>