<?php

!defined('IN_ASK2') && exit('Access Denied');

class bannedmodel {

    var $db;
    var $base;

    function bannedmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($start=0, $limit=20) {
        $bannedlist = array();
        $this->refresh();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "banned` ORDER BY id DESC LIMIT $start,$limit");
        while ($banned = $this->db->fetch_array($query)) {
            $banned['endtime'] = tdate($banned['time'] + $banned['expiration']);
            $banned['starttime'] = tdate($banned['time']);
            if ($banned['ip1'] < 0)
                $banned['ip1'] = '*';
            if ($banned['ip2'] < 0)
                $banned['ip2'] = '*';
            if ($banned['ip3'] < 0)
                $banned['ip3'] = '*';
            if ($banned['ip4'] < 0)
                $banned['ip4'] = '*';
            $banned['ip'] = $banned['ip1'] . '.' . $banned['ip2'] . '.' . $banned['ip3'] . '.' . $banned['ip4'];
            $bannedlist[] = $banned;
        }
        return $bannedlist;
    }

    function add($ips, $expiration) {
        $expiration = ($expiration) ? $expiration * 3600 * 24 : 0;
        list($ip1, $ip2, $ip3, $ip4) = $ips;
        $this->db->query("INSERT INTO `" . DB_TABLEPRE . "banned` (`ip1`,`ip2`,`ip3`,`ip4`,`admin`,`time`,`expiration`) VALUES ('$ip1','{$ip2}','{$ip3}','{$ip4}','{$this->base->user['username']}','{$this->base->time}','{$expiration}')");
        $this->base->cache->remove('banned');
    }

    function remove($ips) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "banned` WHERE id IN (' " . implode("','", $ips) . "')");
        $this->update();
    }

    function refresh() {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "banned` WHERE (`time`+`expiration`)<{$this->base->time}");
    }

    function update() {
        $ips = $this->get_list();
        $this->base->cache->write('banned', $ips);
    }

}

?>