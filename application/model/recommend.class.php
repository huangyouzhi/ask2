<?php

!defined('IN_ASK2') && exit('Access Denied');

class recommendmodel {

    var $db;
    var $base;

    function recommendmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($start = 0, $limit = 10) {
        $recommendlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "recommend  ORDER BY TIME DESC LIMIT $start,$limit");
        while ($recommend = $this->db->fetch_array($query)) {
            $recommend['category_name'] = $this->base->category[$recommend['cid']]['name'];
            $recommend['format_time'] = tdate($recommend['time']);
            $recommend['category_name'] = $this->base->category[$recommend['cid']]['name'];
            $recommend['url'] = url('question/view/' . $recommend['qid'], $recommend['url']);
            $recommend['image'] =$recommend['image']?$recommend['image']:'css/default/recomend.jpg' ;
            $recommendlist[] = $recommend;
        }
        return $recommendlist;
    }

    function add($qids) {
        $time = $this->base->time;
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "question WHERE `id` IN ($qids) AND status=6");
        $addsql = "REPLACE INTO ".DB_TABLEPRE."recommend (`qid`,`cid`,`title`,`description`,`image`,`url`,`time`) VALUES ";
        while ($question = $this->db->fetch_array($query)) {
            $src=getfirstimg($question['description']);
            $strip_titile = cutstr($question['title'], 45);
            $strip_desc = cutstr(strip_tags($question['description']),70);
            $addsql .="(".$question['id'].",".$question['cid'].",'$strip_titile','$strip_desc','$src','".$question['url']."',$time),";
        }
       return $this->db->query(substr($addsql,0,-1));
    }

    function remove($qids) {
        return $this->db->query("DELETE FROM ".DB_TABLEPRE."recommend WHERE qid IN ($qids)");
    }
}

?>
