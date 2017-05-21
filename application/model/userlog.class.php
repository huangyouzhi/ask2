<?php

!defined('IN_ASK2') && exit('Access Denied');

class userlogmodel {

    var $db;
    var $base;

    function userlogmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /**
     * 添加用户操作记录
     * @param enum $type=login|ask|answer
     * @return int  
     */
    function add($type) {
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "userlog(`id`,`sid`,`type`,`time`) VALUES (null,'{$this->base->user['uid']}','$type',{$this->base->time})");
        return $this->db->insert_id();
    }

    /**
     * 按时间计算用户的操作次数
     * @param ENUM $type
     * @param INT $hours
     * @return INT 
     */
    function rownum_by_time($type='ask', $hours=1) {
        $starttime = strtotime(date("Y-m-d H:00:00", $this->base->time));
        $endtime = $starttime + $hours * 3600;
        $sid = $this->base->user['uid'];
      
        return $this->db->fetch_total('userlog', " `time`>$starttime AND `time`<$endtime AND sid='$sid' AND type='$type'");
    }

}

?>
