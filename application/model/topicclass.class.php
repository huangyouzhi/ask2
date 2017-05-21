<?php
!defined('IN_ASK2') && exit('Access Denied');
class topicclassmodel {

    var $db;
    var $base;

    function topicclassmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
/* 获取分类信息 */

    function get_list() {
           $topicclasslist = array();
         
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topicclass");
        while ($cate = $this->db->fetch_array($query)) {
            $topicclasslist[] = $cate;
          
        }
      
        return $topicclasslist;
    }
    
}