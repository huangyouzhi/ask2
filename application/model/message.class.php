<?php

!defined('IN_ASK2') && exit('Access Denied');

class messagemodel {

    var $db;
    var $base;

    function messagemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 读取一条消息内容 */

    function get($id) {
        $message = $this->db->fetch_first('SELECT * FROM ' . DB_TABLEPRE . 'message WHERE `id`=' . $id);
        $message['date'] = tdate($message['time']);
        return $message;
    }

    /* 发送一条消息 */

    function add($msgfrom, $msgfromid, $msgtoid, $subject, $message) {
        $time = $this->base->time;
        $this->db->query('INSERT INTO ' . DB_TABLEPRE . "message  SET `from`='" . $msgfrom . "' , `fromuid`=$msgfromid , `touid`=$msgtoid  , `subject`='" . $subject . "' , `time`=" . $time . " , `content`='" . $message . "'");
        return $this->db->insert_id();
    }

    function list_by_touid($touid, $start = 0, $limit = 10) {
        $messagelist = array();
        $sql = "SELECT * FROM " . DB_TABLEPRE . "message WHERE touid=$touid AND fromuid!=$touid AND status<>2 AND fromuid=0 ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    /* 获取消息列表
      fromuid为0，表示是系统消息
      new：1表示新消息,0为已读消息。
      status:0都没删除；1发消息者删除；2收消息者删除；
     */

    function group_by_touid($touid, $start = 0, $limit = 10) {
        $messagelist = array();
        $sql = "SELECT * FROM (SELECT * FROM " . DB_TABLEPRE . "message  WHERE touid=$touid AND fromuid!=$touid AND status<>2 AND fromuid<>0 ORDER BY `time` DESC) t GROUP BY `from`  ORDER BY `time` desc LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    function rownum_by_touid($touid) {
        $query = $this->db->query("SELECT * FROM (SELECT * FROM ask_message  WHERE touid=$touid AND fromuid!=$touid AND status<>2 AND fromuid<>0  ORDER BY `time` DESC) t GROUP BY `from`");
        return $this->db->num_rows($query);
    }

    function list_by_fromuid($fromuid, $start = 0, $limit = 10) {
        $messagelist = array();
        $sql = "SELECT * FROM " . DB_TABLEPRE . "message WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status IN (0,1)) OR ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status IN (0,2)) ORDER BY time DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $message['touser'] = $this->db->result_first("SELECT username FROM " . DB_TABLEPRE . "user WHERE uid=" . $message['touid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    /* 得到新消息总数 */

    function get_num($uid) {
        $num = $this->db->result_first("SELECT count(*) FROM " . DB_TABLEPRE . "message WHERE touid='$uid' AND msgtoid>0 AND new=1 ");
        return $num;
    }

    /**
     * 0都未删除;1发消息者删除；2收消息者删除；
     * @param type $type
     * @param type $msgids
     */
    function remove($type, $msgids) {
        $messageid = ($msgids && is_array($msgids)) ? implode(",", $msgids) : $msgids;
        if ('inbox' == $type) {
            $this->db->query("DELETE FROM " . DB_TABLEPRE . "message WHERE fromuid=0 AND `id` IN ($messageid)");
            $this->db->query("DELETE FROM " . DB_TABLEPRE . "message WHERE status = 1 AND `id` IN ($messageid)");
            $this->db->query("UPDATE " . DB_TABLEPRE . "message SET status=2 WHERE status=0 AND `id` IN ($messageid)");
        } else {
            $this->db->query("DELETE FROM " . DB_TABLEPRE . "message WHERE status = 2 AND `id` IN ($messageid)");
            $this->db->query("UPDATE " . DB_TABLEPRE . "message SET status=1 WHERE status=0 AND `id` IN ($messageid)");
        }
    }

    /**
     * 根据发件人删除整个对话
     * @param type $authors
     */
    function remove_by_author($authors) {
        foreach ($authors as $fromuid) {
            $this->db->query("DELETE FROM " . DB_TABLEPRE . "message WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status=1)");
            $this->db->query("DELETE FROM " . DB_TABLEPRE . "message WHERE fromuid<>touid AND ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status=2");
            $this->db->query("UPDATE " . DB_TABLEPRE . "message SET status=2 WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status IN (0,1))");
            $this->db->query("UPDATE " . DB_TABLEPRE . "message SET status=1 WHERE fromuid<>touid AND ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status IN (0,2))");
        }
    }

    /* 更新消息为已读状态 */

    function read_by_fromuid($fromuid,$touid=0,$id=0) {
    	if($fromuid!=0){
    		$this->db->query("UPDATE `" . DB_TABLEPRE . "message` set new=0  WHERE `fromuid` =$fromuid AND  `touid` =$touid AND  `id` =$id ");
    	}else{
    		
    		$this->db->query("UPDATE `" . DB_TABLEPRE . "message` set new=0  WHERE `fromuid` =$fromuid AND  `touid` =$touid  ");
    	}
        
    }

    function update_status($id, $status) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "message SET status=$status WHERE id=$id");
    }

 function update_allstatus($touid, $status) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "message SET new=0,status=$status WHERE touid=$touid");
    }
    
    function read_user_recommend($uid, $user_categorys) {
        if (!$user_categorys) {
            return 0;
        }
        $cids = implode(",", $user_categorys);
        $sql = "SELECT id FROM " . DB_TABLEPRE . "question WHERE cid IN ($cids) AND id NOT IN (SELECT qid FROM " . DB_TABLEPRE . "user_readlog WHERE uid=$uid)";
        $query = $this->db->query($sql);
        $questionlist = array();
        while ($question = $this->db->fetch_array($query)) {
            $questionlist[] = $question;
        }
        if ($questionlist) {
            $insertsql = "INSERT INTO " . DB_TABLEPRE . "user_readlog(qid,uid) VALUES ";
            foreach ($questionlist as $question) {
                $insertsql.= "(" . $question['id'] . ",$uid),";
            }
            $this->db->query(substr($insertsql, 0, -1));
        }
    }

    function rownum_user_recommend($uid, $user_categorys, $type = 'all') {
        if (!$user_categorys) {
            return 0;
        }
        $timestart = $this->base->time - 30 * 24 * 3600;
        $cids = implode(",", $user_categorys);
        $sql = "SELECT COUNT(*) FROM " . DB_TABLEPRE . "question WHERE cid IN ($cids) AND authorid<>$uid";
        ($type == 'notread') && $sql.="  AND id NOT IN (SELECT qid FROM " . DB_TABLEPRE . "user_readlog WHERE uid=$uid) AND time>$timestart";
        return $this->db->result_first($sql);
    }

    function list_user_recommend($uid, $user_categorys, $start = 0, $limit = 20) {
        $questionlist = array();
        if (!$user_categorys) {
            return $questionlist;
        }
        $cids = implode(",", $user_categorys);

        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "question WHERE cid IN ($cids) AND authorid<>$uid ORDER BY time DESC LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $question['category_name'] = $this->base->category[$question['cid']]['name'];
              $question['avatar']=get_avatar_dir($question['authorid']);
            $question['url'] = url('question/view/' . $question['id'], $question['url']);
             $question['title']=checkwordsglobal( $question['title']);
             $question['image']=getfirstimg($question['description']);
              $question['description']=cutstr( checkwordsglobal(strip_tags($question['description'])), 240,'...');
            $questionlist[] = $question;
        }
        return $questionlist;
    }

}

?>
