<?php

!defined('IN_ASK2') && exit('Access Denied');

class articlecommentmodel {

    var $db;
    var $base;
    var $statustable = array(
        'all' => ' AND status!=0',
        '0' => ' AND status=0',
        '1' => ' AND status!=0 ',
        '2' => ' AND status!=0 ',
    );

    function articlecommentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 根据aid获取一个答案的内容，暂时无用 */

    function get($id) {
        $answer= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "articlecomment WHERE id='$id'");
        
         if ($answer) {
          
             $answer['title']=checkwordsglobal( $answer['title']);
              $answer['content']=checkwordsglobal( $answer['content']);
        }
        return $answer;
    }

 

   
     function updatecmsupport($cmid){
      $this->db->query("UPDATE " . DB_TABLEPRE . "articlecomment SET supports=supports+1 WHERE id =$cmid");
    
     }
    function list_by_tid($tid, $status, $start = 0, $limit = 5) {
        $answerlist = array();
        $sql = 'SELECT * FROM `' . DB_TABLEPRE . 'articlecomment` WHERE `tid`=' . $tid;
        $sql .=' AND status=1 ' . ' ORDER BY `supports` DESC , `time` DESC   LIMIT ' . $start . ',' . $limit;
     
        $query = $this->db->query($sql);
        while ($answer = $this->db->fetch_array($query)) {
            $answer['time'] = tdate($answer['time']);
             $answer['avatar'] = get_avatar_dir($answer['authorid']);
              $answer['content']=checkwordsglobal( $answer['content']);
            $answerlist[] = $answer;
        }
        return $answerlist;
    }
    //检查用户是否评论过
    function checkhascomment($tid,$uid){
    	  $comment= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "articlecomment WHERE tid=$tid and authorid=$uid");
        return $comment;
    }
 function add_seo($tid, $title, $content,$uid1,$username1, $status = 0,$supports=13) {
try{
 	     $uid =$uid1;// $this->base->user['uid'];
        $username = $username1;//$this->base->user['username'];
        $mtime=time();
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "articlecomment SET tid='$tid',title='$title',author='$username',authorid='$uid',time='$mtime',content='$content',supports='$supports',status=$status,ip='{$this->base->ip}'");
         $aid = $this->db->insert_id();
        $this->db->query("UPDATE " . DB_TABLEPRE . "topic SET  articles=articles+1  WHERE id=" . $tid);
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET articles=articles+1 WHERE  uid =$uid");
       // $aid = $this->db->insert_id();
        return $aid;
}catch (Exception $er){
	return '0';
}
    }
    function ondeletecomment() {
        if (isset($this->post['id'])) {
            $commentid = intval($this->post['id']);
            $this->db->query("DELETE FROM `" . DB_TABLEPRE . "articlecomment ` WHERE `id` IN ($commentid)");
           
            exit('1');
        }
    }
    

}

?>
