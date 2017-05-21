<?php

!defined('IN_ASK2') && exit('Access Denied');

class editormodel {

    var $db;
    var $base;
    var $filelist = array();

    function editormodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        return $this->db->fetch_first('SELECT * FROM ' . DB_TABLEPRE . 'editor WHERE id=' . $id);
    }

    function get_list($available=1) {
        $toolbarlist = array();
        $sql = 'SELECT * FROM ' . DB_TABLEPRE . 'editor';
        $available && $sql.=' where available=1 ';
        $sql.=' ORDER BY displayorder ASC';
        $query = $this->db->query($sql);
        while ($toolbar = $this->db->fetch_array($query)) {
            $toolbarlist[] = $toolbar;
        }
        return $toolbarlist;
    }

    /*
     * '-'会自动换一行
     * '|'自动分割相关功能
     */

    function get_items() {
        $tags = array();
        $query = $this->db->query('SELECT * FROM ' . DB_TABLEPRE . 'editor where available=1 ORDER BY displayorder ASC');
        while ($item = $this->db->fetch_array($query)) {
            $tags[] = $item['tag'];
        }
        return implode(',', $tags);
    }

    function update($id, $available=1) {
        $this->db->query('UPDATE ' . DB_TABLEPRE . 'editor SET available=' . $available . '  WHERE id=' . $id);
    }

    function order($order) {
        $order = explode(',', $order);
        $count = count($order);
        for ($i = 0; $i < $count; $i++) {
            $this->db->query("UPDATE " . DB_TABLEPRE . "editor SET displayorder=$i WHERE id=" . $order[$i]);
        }
    }
}

?>
