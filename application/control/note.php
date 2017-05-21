<?php

!defined('IN_ASK2') && exit('Access Denied');

class notecontrol extends base {

    function notecontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("note");
        $this->load("note_comment");
    }

    /* 前台查看公告列表 */

    function onlist() {
       $navtitle = "本站公告列表";
        $seo_description= "发布".$this->setting['site_name']."最新公告，包括问答升级，维护更新，修改，以及重大变更。";
        $seo_keywords= "公告";
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('note', ' 1=1');
        $notelist = $_ENV['note']->get_list($startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "note/list");
        include template('notelist');
    }

    /* 浏览公告 */

    function onview() {
        $navtitle = '查看公告';
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('note', ' 1=1');
        $note = $_ENV['note']->get($this->get[2]);
        $rownum = $this->db->fetch_total('note_comment', " noteid=" . $note['id']);
        $commentlist = $_ENV['note_comment']->get_by_noteid($note['id'], $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "note/view/" . $note['id']);
        $_ENV['note']->update_views($note['id']);
        $seo_title = $note['title'].' - '.$navtitle.' - '.$this->setting['site_name'];
        $is_followedauthor = $_ENV['user']->is_followed($note['authorid'], $this->user['uid']);
        $seo_description = $seo_title;
        $seo_keywords = $note['title'];
        include template('note');
    }

    function onaddcomment() {
        if (isset($this->post['submit'])) {

      
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()&&$this->user['credit1']<$this->setting['jingyan']) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
        	if($this->user['isblack']==1){
        $this->message('黑名单用户无法评论！', 'BACK');
        	}
        				
            $noteid = intval($this->post['noteid']);
            $_ENV['note_comment']->add($noteid, $this->post['content']);
            $_ENV['note']->update_comments($noteid);
            $this->message("评论添加成功!", "note/view/" . $noteid);
        }
    }

    function ondeletecomment() {
        $commentid = intval($this->get[3]);
        $noteid = intval($this->get[2]);
        $_ENV['note_comment']->remove($commentid, $noteid);
        $this->message("评论删除成功", "note/view/$noteid");
    }

}

?>