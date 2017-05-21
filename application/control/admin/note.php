<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_notecontrol extends base {

    function admin_notecontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('note');
    }

    function ondefault($message = '') {
        if (empty($message))
            unset($message);
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $notelist = $_ENV['note']->get_list($startindex, $pagesize);
        $rownum = $this->db->fetch_total("note", "1=1");
        $departstr = page($rownum, $pagesize, $page, "admin_note/default");
        include template('notelist', 'admin');
    }

    function onadd() {
        if (isset($this->post['submit'])) {
            $title = $this->post['title'];
            $url = $this->post['url'];
            $content = $this->post['content'];
            $_ENV['note']->add($title, $url, $content);
            $this->ondefault('公告添加成功！');
        } else {
            include template('addnote', 'admin');
        }
    }

    function onedit() {
        if (isset($this->post['submit'])) {
            $id = $this->post['id'];
            $title = $this->post['title'];
            $url = $this->post['url'];
            $content = $this->post['content'];
            $_ENV['note']->update($id, $title, $url, $content);
            $this->ondefault('公告编辑成功！');
        } else {
            $note = $_ENV['note']->get($this->get[2]);
            include template('editnote', 'admin');
        }
    }

    function onremove() {
        $message = '没有选择公告！';
        if (isset($this->post['delete'])) {
            $ids = implode(",", $this->post['delete']);
            $_ENV['note']->remove_by_id($ids);
            $message = '公告刪除成功！';
        }
        $this->ondefault($message);
    }

}

?>