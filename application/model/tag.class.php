<?php

!defined('IN_ASK2') && exit('Access Denied');

class tagmodel {

    var $db;
    var $base;

    function tagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_qid($qid) {
        $taglist = array();
        $query = $this->db->query("SELECT DISTINCT name FROM `" . DB_TABLEPRE . "question_tag` WHERE qid=$qid ORDER BY `time` ASC LIMIT 0,10");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag['name'];
        }
        return $taglist;
    }

    function list_by_name($name) {
        return $this->db->fetch_first("SELECT * FROM `" . DB_TABLEPRE . "question_tag` WHERE name='$name'");
    }
    function list_by_countname($name) {
        return $this->db->fetch_first("SELECT count(*) as sum FROM `" . DB_TABLEPRE . "question_tag` WHERE name='$name'");
    }
    function list_by_tagname($tagname,$start = 0, $limit = 100){
    	   $taglist = array();
    	$query=$this->db->query("SELECT  distinct name FROM `" . DB_TABLEPRE . "question_tag` WHERE name like '%$tagname%'  ORDER BY qid DESC LIMIT $start,$limit");
      while ($tag = $this->db->fetch_array($query)) {
      	$tag['count']=$this->list_by_countname($tag['name']);
            $taglist[] = $tag;
        }
          return $taglist;
    }
    
    function get_list($start = 0, $limit = 100) {
        $taglist = array();
       // echo "SELECT count(qid) as questions ,name FROM " . DB_TABLEPRE . "question_tag GROUP BY name ORDER BY qid DESC LIMIT $start,$limit";exit();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "question_tag ORDER BY qid DESC LIMIT $start,$limit");
        while ($tag = $this->db->fetch_array($query)) {
        	$tag['time']=tdate($tag['time']);
            $taglist[] = $tag;
        }
        return $taglist;
    }

    function rownum() {
        $query = $this->db->query("SELECT count(name) FROM " . DB_TABLEPRE . "question_tag GROUP BY name");
        return $this->db->num_rows($query);
    }

    function multi_add($namelist, $qid) {
        if (empty($namelist))
            return false;
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "question_tag WHERE qid=$qid");
        $insertsql = "INSERT INTO " . DB_TABLEPRE . "question_tag(`qid`,`name`,`time`) VALUES ";
        foreach ($namelist as $name) {
            $insertsql .= "($qid,'".  htmlspecialchars($name)."',{$this->base->time}),";
        }
        $this->db->query(substr($insertsql, 0, -1));
    }

    function remove_by_name($names) {
        $namestr = "'" . implode("','", $names) . "'";
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "question_tag WHERE `name` IN ($namestr)");
    }

}

?>
