<?php

!defined('IN_ASK2') && exit('Access Denied');

class favoritemodel {

    var $db;
    var $base;

    function favoritemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_qid($qid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "favorite WHERE `qid`=$qid AND `uid`=" . $this->base->user['uid']);
    }

    function get_by_tid($tid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic_likes WHERE `tid`=$tid AND `uid`=" . $this->base->user['uid']);
    }
    function get_list($start = 0, $limit = 10) {
        $uid = $this->base->user['uid'];
        $questionlist = array();
        $query = $this->db->query("SELECT q.answers,q.title,f.qid,f.id,f.time,f.uid FROM `" . DB_TABLEPRE . "question` as q ,`" . DB_TABLEPRE . "favorite` as f  WHERE q.id=f.qid AND f.uid=$uid  LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }
    function get_list_byalltid($start = 0, $limit = 10) {
        $uid = $this->base->user['uid'];
        $topiclist = array();

        $query = $this->db->query("SELECT t.likes, t.articles,t.title,f.tid,f.id,f.time,f.uid FROM `" . DB_TABLEPRE . "topic` as t ,`" . DB_TABLEPRE . "topic_likes` as f  WHERE t.id=f.tid AND f.uid=$uid  LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
            $topic['format_time'] = tdate($topic['time']);
            $topiclist[] = $topic;
        }
        return $topiclist;
    }
    
    function get_list_byqid($qid,$start = 0, $limit = 10) {
        $uid = $this->base->user['uid'];
        $userlist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "favorite`  WHERE qid=$qid  LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['format_time'] = tdate($user['time']);
              $user['avatar'] =get_avatar_dir($user['uid']);
              $_user=$this->get_by_uid($user['uid']);
               $user['username']=$_user['username'];
             
            $userlist[] = $user;
        }
        return $userlist;
    }
    function get_list_bytid($tid,$start = 0, $limit = 10) {
        $uid = $this->base->user['uid'];
        $userlist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "topic_likes`  WHERE tid=$tid  LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['format_time'] = tdate($user['time']);
              $user['avatar'] =get_avatar_dir($user['uid']);
              $_user=$this->get_by_uid($user['uid']);
               $user['username']=$_user['username'];
             
            $userlist[] = $user;
        }
        return $userlist;
    }
    
    function get_by_uid($uid, $loginstatus = 1) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
    
        return $user;
    }
    function rownum_by_uid($uid = 0) {
        (!$uid) && $uid = $this->base->user['uid'];
        $query = $this->db->query("SELECT count(*) as size  FROM `" . DB_TABLEPRE . "question` as q ,`" . DB_TABLEPRE . "favorite` as f  WHERE q.id=f.qid AND f.uid=$uid ");
        $favorite = $this->db->fetch_array($query);
        return $favorite['size'];
    }

    function add($qid) {
        $uid = $this->base->user['uid'];
        $this->db->query('REPLACE INTO `' . DB_TABLEPRE . "favorite`(`qid`,`uid`,`time`) values ($qid,$uid,{$this->base->time})");
        $this->db->query("UPDATE `" . DB_TABLEPRE . "question` set attentions=attentions+1  WHERE `id` =$qid");
        return $this->db->insert_id();
    }
  function addtopiclikes($tid) {
        $uid = $this->base->user['uid'];
        $this->db->query('REPLACE INTO `' . DB_TABLEPRE . "topic_likes`(`tid`,`uid`,`time`) values ($tid,$uid,{$this->base->time})");
         $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` set likes=likes+1  WHERE `id` =$tid");
        return $this->db->insert_id();
      
    }

   function remove_topiclikes($ids) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "topic_likes` WHERE `id` IN($ids)");
    }
    
    function remove($ids) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "favorite` WHERE `id` IN($ids)");
    }

}

?>
