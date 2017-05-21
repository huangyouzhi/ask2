<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_adcontrol extends base {

    function admin_adcontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load('ad');
    }

    function ondefault() {
        if (isset($this->post['submit'])) {
            $page = $this->post['page'];
            $adlist = $this->post[$page];
            $_ENV['ad']->add($page, $adlist);
            $this->cache->remove('adlist');
        }
        include template("adlist", "admin");
    }

}

?>