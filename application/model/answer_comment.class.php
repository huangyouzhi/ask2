<?php

!defined('IN_ASK2') && exit('Access Denied');

class answer_commentmodel {

    var $db;
    var $base;

    function answer_commentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid, $aid) {
        $answer_comment= $this->db->fetch_first("SELECT * FROM `" . DB_TABLEPRE . "answer_comment` WHERE authorid=$uid AND aid=$aid");
    if ($answer_comment) {
          
            
              $answer_comment['content']=checkwordsglobal( $answer_comment['content']);
        }
        return $answer_comment;
    }

    function get_by_aid($aid, $start = 0, $limit = 10) {
        $commentlist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "answer_comment` WHERE aid=$aid ORDER BY `time` DESC  limit $start,$limit");
        while ($comment = $this->db->fetch_array($query)) {
            $comment['avatar'] = get_avatar_dir($comment['authorid']);
            $comment['format_time'] = tdate($comment['time']);
              $comment['content']=checkwordsglobal(  $comment['content']);
            $commentlist[] = $comment;
        }
        return $commentlist;
    }

    function add($answerid, $conmment,$authorid,$author) {
    	 $conmment=checkwordsglobal( $conmment);
        $this->db->query('INSERT INTO `' . DB_TABLEPRE . "answer_comment`(`aid`,`authorid`,`author`,`content`,`time`) values ($answerid,$authorid,'$author','$conmment'," . $this->base->time . ")");
        $this->db->query("UPDATE " . DB_TABLEPRE . "answer SET comments=comments+1 WHERE `id`=$answerid");
    }

    function remove($commentids, $answerid) {
        $commentcount = 1;
        if (is_array($commentids)) {
            $commentcount = count($commentids);
            $commentids = implode(",", $commentids);
        }
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "answer_comment WHERE `id` IN ($commentids)");
        $this->db->query("UPDATE " . DB_TABLEPRE . "answer SET comments=comments-$commentcount WHERE `id`=$answerid");
    }

}

?>
