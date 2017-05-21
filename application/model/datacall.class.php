<?php

!defined('IN_ASK2') && exit('Access Denied');

class datacallmodel {

    var $db;
    var $base;

    function datacallmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function get($id) {
        return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."datacall WHERE id='$id'");
    }

    function get_list($limit=100) {
        $datacalllist=array();
        $query=$this->db->query("select * from ".DB_TABLEPRE."datacall order by id desc limit $limit");
        while($datacall=$this->db->fetch_array($query)) {
            $datacall['time_format']=tdate($datacall['time']);
            $datacalllist[]=$datacall;
        }
        return $datacalllist;
    }


    function add($title,$expression) {
        $this->db->query('INSERT INTO '.DB_TABLEPRE."datacall(title,expression,time) values ('$title','$expression','{$this->base->time}')");
        return $this->db->insert_id();
    }

    function update($id,$title,$expression) {
        $this->db->query('update  '.DB_TABLEPRE."datacall  set title='$title',expression='$expression',time='{$this->base->time}' where id=$id ");
    }

    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `".DB_TABLEPRE."datacall` WHERE `id` IN ($ids)");
    }

}
?>
