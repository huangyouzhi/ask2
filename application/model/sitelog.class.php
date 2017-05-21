<?php

!defined('IN_ASK2') && exit('Access Denied');

class sitelogmodel {

    var $db;
    var $base;

    function sitelogmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

  

    function get_list($start=0,$limit=1000) {
        $loglist=array();
        $query=$this->db->query("SELECT * FROM `".DB_TABLEPRE."site_log` ORDER BY `time` DESC limit $start,$limit");
        while($log=$this->db->fetch_array($query)) {
            $log['time']=tdate($log['time'],3,0);
            $loglist[]=$log;
        }
        return $loglist;
    }
    function delete($starttime,$endtime){
    	
    	    	 $this->db->query("delete from  `".DB_TABLEPRE."site_log` where time>=$starttime and time <=$endtime");
    }



}
?>
