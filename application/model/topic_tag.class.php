<?php

!defined('IN_ASK2') && exit('Access Denied');

class topic_tagmodel {

    var $db;
    var $base;

    function topic_tagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_aid($aid) {
        $taglist = array();
        $query = $this->db->query("SELECT DISTINCT name FROM `" . DB_TABLEPRE . "topic_tag` WHERE aid=$aid ORDER BY `time` ASC LIMIT 0,10");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag['name'];
        }
        return $taglist;
    }

    function list_by_name($name) {
        return $this->db->fetch_first("SELECT * FROM `" . DB_TABLEPRE . "topic_tag` WHERE name='$name'");
    }
   function list_by_countname($name) {
        return $this->db->fetch_first("SELECT count(*) as sum FROM `" . DB_TABLEPRE . "topic_tag` WHERE name='$name'");
    }
    function list_by_tagname($tagname,$start = 0, $limit = 100){
    	   $taglist = array();
    	$query=$this->db->query("SELECT  distinct name FROM `" . DB_TABLEPRE . "topic_tag` WHERE name like '%$tagname%'  ORDER BY qid DESC LIMIT $start,$limit");
      while ($tag = $this->db->fetch_array($query)) {
      	$tag['count']=$this->list_by_countname($tag['name']);
            $taglist[] = $tag;
        }
          return $taglist;
    }
    function get_list($start = 0, $limit = 100) {
        $taglist = array();
        $query = $this->db->query("SELECT count(aid) as questions ,name FROM " . DB_TABLEPRE . "topic_tag GROUP BY name ORDER BY aid DESC LIMIT $start,$limit");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag;
        }
        return $taglist;
    }

    function rownum() {
        $query = $this->db->query("SELECT count(name) FROM " . DB_TABLEPRE . "topic_tag GROUP BY name");
        return $this->db->num_rows($query);
    }

    function multi_add($namelist, $aid) {
    	
        if (empty($namelist))
            return false;
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "topic_tag WHERE aid=$aid");
        $insertsql = "INSERT INTO " . DB_TABLEPRE . "topic_tag(`aid`,`name`,`time`) VALUES ";
        foreach ($namelist as $name) {
            $insertsql .= "($aid,'".  htmlspecialchars($name)."',{$this->base->time}),";
        }
       
        $this->db->query(substr($insertsql, 0, -1));
    }

    function remove_by_name($names) {
        $namestr = "'" . implode("','", $names) . "'";
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "topic_tag WHERE `name` IN ($namestr)");
    }

}

?>
