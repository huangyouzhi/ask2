<?php

!defined('IN_ASK2') && exit('Access Denied');

class plugin_sitemapcontrol extends base {

    function plugin_sitemapcontrol(& $get, & $post) {
        $this->base($get,$post);
       
    }

    function ondefault() {
       include 'plugin/sitemap/sitemap.php';
       
    }

}

?>