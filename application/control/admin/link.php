<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_linkcontrol extends base {

    function admin_linkcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('link');
    }

    function ondefault($message='') {
        if(empty($message)) unset($message);
        $linklist = $_ENV['link']->get_list(0,100);
        include template('linklist','admin');
    }

    function onadd() {
        if(isset($this->post['submit'])) {
            $name = $this->post['name'];
            $desrc = $this->post['descr'];
            $url = $this->post['url'];
            $logo = $this->post['logo'];
            if(!$name || !$url) {
                $type='errormsg';
                $message = '链接名称或链接地址不能为空!';
                include template('addlink','admin');
                exit;
            }
            $_ENV['link']->add($name,$url,$desrc,$logo);
            $this->cache->remove('link');
            $this->ondefault('链接添加成功！');
        }else {
            include template('addlink','admin');
        }
    }

    function onedit() {
    	$lid = isset($this->post['lid'])?intval($this->post['lid']):intval($this->get[2]);
        if(isset($this->post['submit'])) {
            $name = $this->post['name'];
            $desrc = $this->post['descr'];
            $url = $this->post['url'];
            $logo = $this->post['logo'];
            if(!$name || !$url) {
                $type='errormsg';
                $message = '链接名称或链接地址不能为空!';
                $curlink = $_ENV['link']->get($lid);
                include template('addlink','admin');
            }
            $_ENV['link']->update($name,$url,$desrc,$logo,$lid);
            $this->cache->remove('link');
            $this->ondefault('链接修改成功！');
        }else {
            $curlink = $_ENV['link']->get($lid);
            include template('addlink','admin');
        }
    }

    function onremove() {
        $_ENV['link']->remove_by_id(intval($this->get[2]));
        $this->cache->remove('link');
        $this->ondefault('链接刪除成功！');
    }

    function onreorder() {
        $orders = explode(",",$this->post['order']);
        $hid = intval($this->post['hiddencid']);
        foreach($orders as $order => $lid) {
            $_ENV['link']->order_link(intval($lid),$order);
        }
    }

}
?>