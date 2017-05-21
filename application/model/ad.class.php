<?php

!defined('IN_ASK2') && exit('Access Denied');
class admodel {

    var $db;
    var $base;
    var $statustable = array(
        'all' => ' AND status!=0',
        '0' => ' AND status=0',
        '1' => ' AND status!=0 AND adopttime=0',
        '2' => ' AND status!=0 AND adopttime>0',
    );

    function admodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list() {
        $adlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "ad LIMIT 0,100");
        while ($ad = $this->db->fetch_array($query)) {
            $adlist[$ad['page']][$ad['position']] = $ad['html'];
        }
        return $adlist;
    }

    function add($page, $adlist) {
        $sql = "REPLACE INTO ".DB_TABLEPRE."ad(`page`,`position`,`html`) VALUES ";
        foreach ($adlist as $position => $html) {
            $sql .="('$page','$position','$html'),";
        }
        $this->db->query(substr($sql, 0, -1));
    }

}

?>
