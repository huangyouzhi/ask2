<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_keywordscontrol extends base {

    function admin_keywordscontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
       $this->load("keywords");
    }

    function ondefault($message = '') {
      
  $this->cache->remove('keyword');
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $wordlist = $_ENV['keywords']->get_list($startindex, $pagesize);
        $rownum = $this->db->fetch_total("keywords"," 1=1");
        $departstr = page($rownum, $pagesize, $page, "admin_keywords/default");
        include template('keywordlist', 'admin');
    }
 
    function onadd() {
        if (isset($this->post['submit']) && $this->post['id']) {
            $ids = implode(",", $this->post['id']);
            $_ENV['keywords']->remove_by_id($ids);
            $message = "删除成功!";
        } else {
            $_ENV['keywords']->add($this->post['wid'], $this->post['find'], $this->post['replacement'], $this->user['username']);
            $message = "修改成功!";
        }
        $this->ondefault($message);
    }
 
    function oneditindexkeyword(){
         if (isset($this->post['submit'])) {
           $this->setting['maxindex_keywords'] = $this->post['maxindex_keywords'];
            $this->setting['pagemaxindex_keywords'] = $this->post['pagemaxindex_keywords'];
           
          
            $_ENV['setting']->update($this->setting);
            $message = '设置更新成功！';
        }
        $this->ondefault($message);
    }
 

    function onmuladd() {
        if (isset($this->post['submit'])) {
            $lines = explode("\n", $this->post['badwords']);
            $_ENV['keywords']->multiadd($lines, $this->user['username']);
            $this->ondefault("添加成功!");
        } else {
            include template('addkeyword', "admin");
        }
    }

}

?>