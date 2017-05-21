<?php

!defined('IN_ASK2') && exit('Access Denied');

class linkmodel {

    var $db;
    var $base;

    function linkmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."link WHERE id='$id'");
    }

    function get_list($start=0,$limit=1000) {
        $linklist=array();
        $query=$this->db->query("SELECT * FROM `".DB_TABLEPRE."link` ORDER BY `displayorder` ASC,`id` ASC limit $start,$limit");
        while($link=$this->db->fetch_array($query)) {
            $link['stitle']=substr($link['name'],0,24);
            $linklist[]=$link;
        }
        return $linklist;
    }


    function add($name,$url,$desrc='',$logo='') {
        $this->db->query('REPLACE INTO `'.DB_TABLEPRE."link`(`name`,`url`,`description`,`logo`) values ('$name','$url','$desrc','$logo')");
        return $this->db->insert_id();
    }

    function update($name,$url,$desrc='',$logo='',$id) {
        $this->db->query('UPDATE  `'.DB_TABLEPRE."link`  set `name`='$name',`url`='$url',`description`='$desrc',`logo`='$logo' where id=$id ");
    }

    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `".DB_TABLEPRE."link` WHERE `id` IN ($ids)");
    }
    function order_link($id,$order) {
        $this->db->query("UPDATE `".DB_TABLEPRE."link` SET 	`displayorder` = '{$order}' WHERE `id` = '{$id}'");
    }

}
?>
