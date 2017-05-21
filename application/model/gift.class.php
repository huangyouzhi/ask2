<?php

!defined('IN_ASK2') && exit('Access Denied');

class giftmodel {

    var $db;
    var $base;

    function giftmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "gift WHERE id='$id'");
    }

    function get_list($start = 0, $limit = 10) {
        $giftlist = array();
        $query = $this->db->query("select * from " . DB_TABLEPRE . "gift order by time desc limit $start,$limit");
        while ($gift = $this->db->fetch_array($query)) {
            $gift['time'] = tdate($gift['time'], 3, 0);
            $giftlist[] = $gift;
        }
        return $giftlist;
    }

    function get_by_range($from, $to, $start = 0, $limit = 10) {
        $giftlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "gift WHERE `credit`>=$from AND `credit`<=$to ORDER BY `time` DESC LIMIT $start,$limit");
        while ($gift = $this->db->fetch_array($query)) {
            $gift['time'] = tdate($gift['time'], 3, 0);
            $giftlist[] = $gift;
        }
        return $giftlist;
    }

    function get_by_range_name($ranges, $name = '', $start = 0, $limit = 10) {
        $giftlist = array();
        $rangesql = '';
        (count($ranges) > 1) && $rangesql = "AND `credit`>=$ranges[0] AND `credit`<=$ranges[1]";
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "gift WHERE  `title` LIKE '$name%' $rangesql ORDER BY `time` DESC LIMIT $start,$limit");
        while ($gift = $this->db->fetch_array($query)) {
            $gift['time'] = tdate($gift['time'], 3, 0);
            $giftlist[] = $gift;
        }
        return $giftlist;
    }

    function add($title, $description, $image, $credit) {
        $this->db->query('INSERT INTO ' . DB_TABLEPRE . "gift(title,description,image,credit,time) values ('$title','$description','$image',$credit,'{$this->base->time}')");
        return $this->db->insert_id();
    }

    function update($title, $desrc, $filepath, $credit, $id) {
        $this->db->query('update  ' . DB_TABLEPRE . "gift  set title='$title',description='$desrc',image='$filepath',credit=$credit where id=$id ");
    }

    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "gift` WHERE `id` IN ($ids)");
    }

    function update_available($id, $available) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "gift SET `available`=$available WHERE `id`in ($id)");
    }

    function addlog($uid, $gid, $username, $realname, $email, $phone, $address, $postcode, $giftname, $qq, $notes, $credit) {

        $this->db->query("INSERT INTO " . DB_TABLEPRE . "giftlog SET `uid`=$uid,`gid`=$gid,`notes`='$notes',`email`='$email',`qq`='$qq',`phone`='$phone',`postcode`='$postcode',`address`='$address',`username`='$username',`realname`='$realname',`giftname`='$giftname',`credit`=$credit,`time`=" . $this->base->time);
    }

    function getlog($logid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "giftlog WHERE id='$logid'");
    }

    function getloglist($start = 0, $limit = 10) {
        $loglist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "giftlog ORDER BY `time` DESC LIMIT $start,$limit");
        while ($log = $this->db->fetch_array($query)) {
            $log['time'] = tdate($log['time'], 3, 0);
            $loglist[] = $log;
        }
        return $loglist;
    }

    function update_gift_status($ids, $status = 1) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "giftlog SET `status`=$status WHERE `status`!=$status AND `id` IN ($ids)");
    }

    function list_by_searchlog($pricerange, $giftname, $username, $datestart, $dateend, $start = 0, $limit = 10) {
        $sql = "SELECT * FROM `" . DB_TABLEPRE . "giftlog` WHERE 1=1 ";
        $giftname && $sql.=" AND `giftname` LIKE '$giftname%' ";
        $username && $sql.="AND `username` LIKE '$username%' ";
        $datestart && ($sql .= " AND `time` >= " . strtotime($datestart));
        $dateend && ($sql .=" AND `time` <= " . strtotime($dateend));
        if ($pricerange && ($pricerange != 'all')) {
            $ranges = explode("-", $pricerange);
            print_r($ranges);
            $sql.=" AND `credit`>" . intval($ranges[0]) . " AND `credit`<= " . intval($ranges[1]);
        }
        $sql.=" ORDER BY `time` DESC LIMIT $start,$limit";
        $giftloglist = array();
        $query = $this->db->query($sql);
        while ($log = $this->db->fetch_array($query)) {
            $log['time'] = tdate($log['time'], 3, 0);
            $giftloglist[] = $log;
        }
        return $giftloglist;
    }

    function rownum_by_searchlog($pricerange, $giftname, $username, $datestart, $dateend) {
        $condition = " 1=1 ";
        $giftname && $condition.=" AND `giftname` LIKE '$giftname%' ";
        $username && $condition.="AND `username` LINKE '$username%' ";
        $datestart && ($condition .= " AND `time` >= " . strtotime($datestart));
        $dateend && ($condition .=" AND `time` <= " . strtotime($dateend));
        if ($pricerange && ($pricerange != 'all')) {
            $ranges = explode("-", $pricerange);
            $condition.=" AND `credit`>$range[0] AND `credit`<= $ranges[1] ";
        }

        return $this->db->fetch_total('giftlog', $condition);
    }

}

?>
