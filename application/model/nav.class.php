<?php

!defined('IN_ASK2') && exit('Access Denied');

class navmodel {

    var $db;
    var $base;

    function navmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "nav WHERE id='$id'");
    }

    function get_list($start = 0, $limit = 1000) {
        $navlist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "nav` ORDER BY `displayorder` ASC,`id` ASC limit $start,$limit");
        while ($nav = $this->db->fetch_array($query)) {
            $navlist[] = $nav;
        }
        return $navlist;
    }

    function add($name, $url, $title = '', $target = 0, $aval = 1, $type = 2) {
        $this->db->query('REPLACE INTO `' . DB_TABLEPRE . "nav`(`name`,`url`,`title`,`target`,`available`,`type`) values ('$name','$url','$title',$target,$aval,$type)");
        return $this->db->insert_id();
    }

    function update($name, $url, $title = '', $target = 0,$type=2, $id) {
        $this->db->query('UPDATE  `' . DB_TABLEPRE . "nav`  set `name`='$name',`url`='$url',`title`='$title',`target`='$target',`type`=$type where id=$id ");
    }

    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "nav` WHERE `id` IN ($ids)");
    }

    function order_nav($id, $order) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "nav` SET 	`displayorder` = '{$order}' WHERE `id` = '{$id}'");
    }

    function update_available($id, $available) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "nav` SET 	`available` = '{$available}' WHERE `id` = '{$id}'");
    }

    function get_format_url() {
        $navlist = $this->get_list();
        foreach ($navlist as &$nav) {
            if (!stristr($nav['url'], "http://")) {
                if ($nav['url'] == 'index/default') {
                    $nav['format_url'] = SITE_URL;
                } else {
                    $nav['format_url'] = url($nav['url'], 1);
                }
            } else {
                $nav['format_url'] = $nav['url'];
            }
        }
        return $navlist;
    }
}

?>
