<?php

!defined('IN_ASK2') && exit('Access Denied');

class note_commentmodel {

    var $db;
    var $base;

    function note_commentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_noteid($noteid, $start = 0, $limit = 10) {
        $commentlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "note_comment WHERE noteid='$noteid' ORDER BY `time` DESC LIMIT $start,$limit");
        while ($comment = $this->db->fetch_array($query)) {
            $comment['avatar'] = get_avatar_dir($comment['authorid']);
            $comment['format_time'] = tdate($comment['time']);
                $comment['content'] = checkwordsglobal($comment['content']);
            $commentlist[] = $comment;
        }
        return $commentlist;
    }

    function add($noteid, $content) {
        $username = $this->base->user['username'];
        $uid = $this->base->user['uid'];
        $this->db->query('INSERT INTO ' . DB_TABLEPRE . "note_comment(noteid,authorid,author,content,time) values ('$noteid','$uid','$username','$content','{$this->base->time}')");
        return $this->db->insert_id();
    }

    function remove($commentid, $noteid) {
        $sql = "DELETE FROM " . DB_TABLEPRE . "note_comment WHERE `id`=" . $commentid;
        if (($this->base->user['grouptype'] != 1)) {
            $sql.=" AND authorid=" . $this->base->user['uid'];
        }
        if ($this->db->query($sql)) {
            $this->db->query("UPDATE " . DB_TABLEPRE . "note SET comments=comments-1 WHERE `id`=$noteid");
        }
    }

}

?>
