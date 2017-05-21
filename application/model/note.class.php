<?php

!defined('IN_ASK2') && exit('Access Denied');

class notemodel {

    var $db;
    var $base;

    function notemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        $note = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "note WHERE id='$id'");
        $note['format_time'] = tdate($note['time'], 3, 0);
           $note['title'] = checkwordsglobal($note['title']);
        $note['content'] = checkwordsglobal($note['content']);
          $note['artlen'] = strlen(strip_tags($note['content']));
          $note['avatar']=get_avatar_dir($note['authorid']);
        return $note;
    }

    function get_list($start = 0, $limit = 10) {
        $notelist = array();
        $query = $this->db->query("select * from " . DB_TABLEPRE . "note order by id desc limit $start,$limit");
        while ($note = $this->db->fetch_array($query)) {
            $note['format_time'] = tdate($note['time'], 3, 0);
              $note['title'] = checkwordsglobal($note['title']);
       $note['avatar']=get_avatar_dir($note['authorid']);
          $note['image']=getfirstimg($note['content']);
              $note['content']=cutstr( checkwordsglobal(strip_tags($note['content'])), 240,'...');
              
            $notelist[] = $note;
        }
        return $notelist;
    }

    function add($title, $url, $content) {
        $username = $this->base->user['username'];
        $uid = $this->base->user['uid'];
        $this->db->query('INSERT INTO ' . DB_TABLEPRE . "note(title,authorid,author,url,content,time) values ('$title','$uid','$username','$url','$content','{$this->base->time}')");
        return $this->db->insert_id();
    }

    function update_views($noteid) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "note SET views=views+1 WHERE `id`='$noteid'");
    }

    function update_comments($noteid) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "note SET comments=comments+1 WHERE `id`='$noteid'");
    }

    function update($id, $title, $url, $content) {
        $username = $this->base->user['username'];
        $this->db->query('update  ' . DB_TABLEPRE . "note  set title='$title',author='$username',url='$url',content='$content',time='{$this->base->time}' where id=$id ");
    }

    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "note` WHERE `id` IN ($ids)");
    }

}

?>
