<?php

!defined('IN_ASK2') && exit('Access Denied');

class famousmodel {

    var $db;
    var $base;

    function famousmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($answersize=3, $start=0, $limit=3) {
        $famouslist = array();
        $query = $this->db->query("SELECT u.uid,u.username,u.questions,u.answers,u.signature,u.credit1,u.credit2,u.credit3,f.id,f.reason,f.time FROM " . DB_TABLEPRE . "famous as f," . DB_TABLEPRE . "user as u WHERE f.uid=u.uid ORDER BY f.id DESC LIMIT $start ,$limit");
        while ($famous = $this->db->fetch_array($query)) {
            $famous['avatar'] = get_avatar_dir($famous['uid']);
            $famous['time'] = tdate($famous['time']);
            $famous['bestanswer'] = $this->get_solve_answer($famous['uid'], 0, $answersize);
            $famouslist[] = $famous;
        }
        return $famouslist;
    }

    function get_solves($start=0, $limit=20) {
        $solvelist = array();
        $query = $this->db->query("SELECT a.qid,a.title FROM " . DB_TABLEPRE . "answer  as a ,`" . DB_TABLEPRE . "famous` as f WHERE a.authorid=f.uid ORDER BY a.time DESC LIMIT $start ,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

    function add($uid, $resaon) {
        if ($this->get_by_uid($uid))
            $this->db->query("UPDATE " . DB_TABLEPRE . "famous SET `reason`='$resaon' ,`time`=" . $this->base->time . " WHERE uid=$uid");
        else
            $this->db->query("INSERT INTO " . DB_TABLEPRE . "famous (`uid` ,`reason` ,`time` ) VALUES ($uid,'$resaon'," . $this->base->time . ")");
    }

    function get_by_uid($uid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "famous WHERE uid=$uid");
    }

    function remove($uid) {
        return $this->db->query("DELETE FROM " . DB_TABLEPRE . "famous WHERE uid = $uid");
    }

    function get_solve_answer($uid, $start=0, $limit=3) {
        $solvelist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "answer` WHERE `authorid`=" . $uid . " AND `adopttime`>0 ORDER BY `adopttime` DESC,`support` DESC LIMIT $start,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

}

?>
