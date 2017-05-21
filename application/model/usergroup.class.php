<?php

!defined('IN_ASK2') && exit('Access Denied');

class usergroupmodel {

    var $db;
    var $base;

    function usergroupmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function get($groupid) {
        return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."usergroup WHERE groupid='$groupid'");
    }

    function add($grouptitle,$grouptype=2,$creditslower=0,$questionlimits=0,$answerlimits=0,$regulars='') {
        $this->db->query('insert into '.DB_TABLEPRE."usergroup  set grouptitle='$grouptitle',creditslower='$creditslower',questionlimits=$questionlimits,answerlimits=$answerlimits,regulars='$regulars',grouptype='$grouptype' ");
    }

    function update($groupid,$group) {
        $this->db->query('update  '.DB_TABLEPRE."usergroup  set grouptitle='$group[grouptitle]',creditslower='$group[creditslower]',creditshigher='$group[creditshigher]',doarticle=$group[doarticle],articlelimits=$group[articlelimits],questionlimits=$group[questionlimits],answerlimits=$group[answerlimits],credit3limits=$group[credit3limits],regulars='$group[regulars]' where groupid=$groupid ");
    }
    /**
     * 得到用户组信息
     *
     * @param int $grouptype
     * @param int $id 系统超级管理员id
     * @return array $grouplist
     */
    function get_list($grouptype=2) {
        $grouplist = array();
        if(is_array($grouptype)){
            $grouptype = implode(",", $grouptype);
        }
        $query = $this->db->query("SELECT * FROM `" .DB_TABLEPRE."usergroup` WHERE grouptype IN ($grouptype) ORDER BY `groupid`  ");
        while($group = $this->db->fetch_array($query)) {
            $grouplist[] = $group;
        }
        return $grouplist;
    }

    function remove($groupid) {
        return $this->db->fetch_first("DELETE FROM ".DB_TABLEPRE."usergroup WHERE groupid='$groupid'");
    }

}
?>
