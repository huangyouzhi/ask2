<?php

!defined('IN_ASK2') && exit('Access Denied');

class expertmodel {

    var $db;
    var $base;

    function expertmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($showquestion=0, $start=0, $limit=3) {
        $expertlist = array();
        $query = $this->db->query("SELECT *  FROM " . DB_TABLEPRE . "user WHERE expert=1 ORDER BY credit1 DESC,answers DESC LIMIT $start ,$limit");
        while ($expert = $this->db->fetch_array($query)) {
            $expert['avatar'] = get_avatar_dir($expert['uid']);
            $expert['lastlogin'] = tdate($expert['lastlogin']);
            $expert['category'] = $this->get_category($expert['uid']);
            $showquestion && $expert['bestanswer'] = $this->get_solve_answer($expert['uid'], 0, 6);
            $expertlist[] = $expert;
        }
        return $expertlist;
    }

    function get_by_uid($uid) {
        return $this->db->fetch_array($this->db->query("SELECT * FROM " . DB_TABLEPRE . "expert WHERE `uid`=$uid"));
    }
 function getusername_by_uid($uid) {
        return $this->db->fetch_array($this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE `uid`=$uid"));
    }

    function get_by_username($username) {
        return $this->db->fetch_array($this->db->query("SELECT * FROM " . DB_TABLEPRE . "expert WHERE `username`='$username'"));
    }

    function get_by_cid($cid, $start=0, $limit=10) {
        $expertlist = array();
        $query = ($cid == 'all') ? $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid IN (SELECT uid FROM " . DB_TABLEPRE . "expert) ORDER BY answers DESC LIMIT $start,$limit") : $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid IN (SELECT uid FROM " . DB_TABLEPRE . "expert WHERE cid=$cid) ORDER BY answers DESC  LIMIT $start,$limit");
        while ($expert = $this->db->fetch_array($query)) {
            $expert['avatar'] = get_avatar_dir($expert['uid']);
            $expertlist[] = $expert;
        }
        return $expertlist;
    }
    function getlist_by_cid($cid){
    	
    	 $expertlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user_category WHERE cid=$cid");
        while ($expert = $this->db->fetch_array($query)) {
          
            $expertlist[] = $expert;
        }
        return $expertlist;
    }

    function add($uid, $cids) {
        $sql = "INSERT INTO " . DB_TABLEPRE . "user_category(`uid`,`cid`) VALUES ";
        foreach ($cids as $cid) {
            $sql .= "($uid,$cid),";
        }
        $this->db->query(substr($sql, 0, -1));
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `expert`=1 WHERE uid=$uid");
    }

    function remove($uids) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `expert`=0 WHERE uid IN ($uids)");
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "user_category WHERE uid IN ($uids)");
    }

    function get_category($uid) {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user_category WHERE uid=$uid");
        $categorylist = array();
        while ($category = $this->db->fetch_array($query)) {
            $category['categoryname'] = $this->base->category[$category['cid']]['name'];
            $categorylist[] = $category;
        }
        return $categorylist;
    }

    function get_solves($start=0, $limit=20) {
        $solvelist = array();
        $query = $this->db->query("SELECT a.qid,a.title FROM " . DB_TABLEPRE . "answer  as a ,`" . DB_TABLEPRE . "expert` as f WHERE a.authorid=f.uid ORDER BY a.time DESC LIMIT $start ,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

    function get_solve_answer($uid, $start=0, $limit=3) {
        $solvelist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "answer` WHERE `authorid`=" . $uid . "  ORDER BY `adopttime` DESC,`supports` DESC LIMIT $start,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

}

?>
