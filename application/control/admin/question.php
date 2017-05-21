<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_questioncontrol extends base {

    function admin_questioncontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load("question");
        $this->load("category");
        $this->load("answer");
        $this->load("recommend");
    }

    function ondefault() {
        $this->onsearchquestion();
    }

    function onsearchquestion($msg='', $ty='') {
        $srchtitle = trim(isset($this->get[2]) ? urldecode($this->get[2]) : $this->post['srchtitle']);
        $srchauthor = trim(isset($this->get[3]) ? urldecode($this->get[3]) : $this->post['srchauthor']);
        $srchdatestart = isset($this->get[4]) ? $this->get[4] : $this->post['srchdatestart'];
        
  
        $srchdateend = isset($this->get[5]) ? $this->get[5] : $this->post['srchdateend'];
        $srchstatus = isset($this->get[6]) ? $this->get[6] : $this->post['srchstatus'];
        $srchcategory = isset($this->get[7]) ? $this->get[7] : $this->post['srchcategory'];
        @$page = max(1, intval($this->get[8]));
        $pagesize =isset($this->setting['admin_list_default']) ? $this->setting['admin_list_default']:$this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $questionlist = $_ENV['question']->list_by_search($srchtitle, $srchauthor, $srchdatestart, $srchdateend, $srchstatus,$srchcategory,$startindex, $pagesize);
        $rownum = $_ENV['question']->rownum_by_search($srchtitle, $srchauthor, $srchdatestart, $srchdateend, $srchstatus,$srchcategory);
        $departstr = page($rownum, $pagesize, $page, "admin_question/searchquestion/$srchtitle/$srchauthor/$srchdatestart/$srchdateend/$srchstatus/$srchcategory");
        $msg && $message = $msg;
        $ty && $type = $ty;
        $catetree = $_ENV['category']->get_categrory_tree($_ENV['category']->get_list());
        include template('questionlist', 'admin');
    }

    function onsearchanswer($msg='', $ty='') {
        $srchtitle = isset($this->get[2]) ? urldecode($this->get[2]) : $this->post['srchtitle'];
        $srchauthor = isset($this->get[3]) ? urldecode($this->get[3]) : $this->post['srchauthor'];
        $srchdatestart = isset($this->get[4]) ? $this->get[4] : $this->post['srchdatestart'];
        $srchdateend = isset($this->get[5]) ? $this->get[5] : $this->post['srchdateend'];
        $keywords = isset($this->get[6]) ? urldecode($this->get[6]) : $this->post['keywords'];
        @$page = max(1, intval($this->get[7]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $answerlist = $_ENV['answer']->list_by_search($srchtitle, $srchauthor, $keywords, $srchdatestart, $srchdateend, $startindex, $pagesize);
        $rownum = $_ENV['answer']->rownum_by_search($srchtitle, $srchauthor, $keywords, $srchdatestart, $srchdateend);
        $departstr = page($rownum, $pagesize, $page, "admin_question/searchanswer/$srchtitle/$srchauthor/$srchdatestart/$srchdateend/$keywords");
        $msg && $message = $msg;
        $ty && $type = $ty;
        include template('answerlist', 'admin');
    }

    function onremovequestion() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->remove($qids);
        }
        $this->ondefault();
    }

    function onremoveanswer() {
        if (isset($this->post['aid'])) {
            $aids = implode(",", $this->post['aid']);
            $_ENV['answer']->remove($aids);
        }
        $this->onsearchanswer();
    }

    function onedit() {
        $qid = isset($this->post['submit']) ? $this->post['qid'] : $this->get[2];
        if (isset($this->post['submit'])) {
            $title = $this->post['title'];
            $description = $this->post['description'];
            $cid1 = $this->post['classlevel1'];
            $cid2 = $this->post['classlevel2'];
            $cid3 = $this->post['classlevel3'];
            $cid = $this->post['cid'];
            $hidden = intval(isset($this->post['hidden']));
            $price = intval($this->post['price']);
            $status = intval(isset($this->post['status']));
            $_ENV['question']->update($qid, $title, $description, $hidden, $price, $status, $cid, $cid1, $cid2, $cid3, $this->post['time']);
            $message = '问题编辑成功!';
        }
        $question = $_ENV['question']->get($qid);
        $question['date'] = date("Y-m-d", $question['time']);
        $question_status = array(array(0, '未审核'), array(1, '待解决'), array(6, '推荐问题'), array(9, '已关闭问题'));
        $prices = array(0, 5, 10, 15, 20, 30, 50, 80, 100);
        include template('editquestion', 'admin');
    }

    function oneditanswer() {
        $aid = isset($this->post['submit']) ? $this->post['aid'] : $this->get[2];
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            $answertime = strtotime($this->post['time']);
            $_ENV['answer']->update_time_content($aid, $answertime, $content);
        }
        $answer = $_ENV['answer']->get($aid);
        $answer['date'] = date("Y-m-d", $answer['time']);
        include template('editanswer', 'admin');
    }

    //回答审核
    function onverifyanswer() {
        if (isset($this->post['aid'])) {
            $aids = implode(",", $this->post['aid']);
            $_ENV['answer']->change_to_verify($aids);
            $type='correctmsg';
            $message = '回答审核完成!';
        }
        @$page = max(1, intval($this->get[2]));
        $pagesize = 20;
        $startindex = ($page - 1) * $pagesize;
        $answerlist = $_ENV['answer']->list_by_condition('`status`=0', $startindex, $pagesize);
        $rownum = $this->db->fetch_total('answer', ' `status`=0');
        $departstr = page($rownum, $pagesize, $page, "admin_question/verifyanswer");
        include template("verifyanswers", "admin");
    }

    //问题审核
    function onverify() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->change_to_verify($qids);
            $this->onexamine('问题审核成功!');
            exit;
        }
    }
   /*百度推送*/
    
    function onbaidutui(){
    	
    	$urls=array();
      $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
         $fix= $this->setting['seo_suffix'];
    	 if (isset($this->post['qid'])){
    	 	//SITE_URL.$suffix."q-$item[id]$fix
    	 	$qids=$this->post['qid'];
    	 	$q_size=count($qids);
    	 	for($i=0;$i<$q_size;$i++){
    	 		array_push($urls, SITE_URL.$suffix."q-".$qids[$i].$fix);
    	 	}
    	 }else{
    	 	 $this->onsearchquestion('您还没选择推送问题!');
    	 }
    	if(trim($this->setting['baidu_api'])!=''&&$this->setting['baidu_api']!=null){
 
$api = $this->setting['baidu_api'];
$result=baidusend($api,$urls);
$this->onsearchquestion('问题推送成功!');
    	}else{
    		 $this->onsearchquestion('问题推送不成功，您还没设置百度推送的api地址，前往系统设置--seo设置里配置!');
    	}
    }
    

    //问题推荐
    function onrecommend() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->change_recommend($qids, 6, 2);
            $this->onsearchquestion('问题推荐成功!');
            exit;
        }
    }

    //取消推荐
    function oninrecommend() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->change_recommend($qids, 2, 6);
            $this->onsearchquestion('取消问题推荐成功!');
            exit;
        }
    }

    //关闭问题
    function onclose() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->update_status($qids, 9);
            $this->onsearchquestion('问题关闭成功!');
            exit;
        }
    }

    //删除问题
    function ondelete() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
//                   if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'){
//            $count=count($this->post['qid']);
//            $qarr=$this->post['qid'];
//            for($i=0;$i<=$count;$i++){
//            	 
//            	     $question = $_ENV['question']->get($qarr[$i]);
//               $touser = $_ENV['user']->get_by_uid($question['authorid']);
//         
//                	sendmail($touser, '您的问题'.$question['title'].'已被删除');
//                
//            }
//                   }
            $_ENV['question']->remove($qids);
            $this->onsearchquestion('问题删除成功!');
            exit;
        }
    }

    //修改问题标题
    function onrenametitle() {
        if (isset($this->post['title'])) {
            $title = trim($this->post['title']);
            if ('' == $title) {
                $this->onsearchquestion('问题标题不能为空!', 'errormsg');
            } else {
                $_ENV['question']->renametitle(intval($this->post['qid']), $title);
                $this->onsearchquestion('问题编辑成功!');
            }
        }
    }

    //修改问题内容
    function oneditquescont() {
        if (isset($this->post['content'])) {
            $content = trim($this->post['content']);
            if ('' == $content) {
                $this->onsearchquestion('问题内容不能为空!', 'errormsg');
                exit;
            }
            $_ENV['question']->update_content(intval($this->post['qid']), $content);
            $this->onsearchquestion('问题内容修改成功!');
        }
    }

    //移动分类
    function onmovecategory() {
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
            $qids = $this->post['qids'];
            $category = $this->cache->load('category');
            if ($category[$cid]['grade'] == 1) {
                $cid1 = $cid;
            } else if ($category[$cid]['grade'] == 2) {
                $cid2 = $cid;
                $cid1 = $category[$cid]['pid'];
            } else if ($category[$cid]['grade'] == 3) {
                $cid3 = $cid;
                $cid2 = $category[$cid]['pid'];
                $cid1 = $category[$cid2]['pid'];
            } else {
                $this->onsearchquestion('分类不存在，请更下缓存!', 'errormsg');
                exit;
            }
            $_ENV['question']->update_category($qids, $cid, $cid1, $cid2, $cid3);
            $this->onsearchquestion('问题分类修改成功!');
            exit;
        }
    }

    //设为未解决
    function onnosolve() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->change_to_nosolve($qids);
            $this->onsearchquestion('问题状态设置成功!');
            exit;
        }
        $this->onsearchquestion();
    }
    //设为已解决
    function onsolve() {
        if (isset($this->post['qid'])) {
            $qids = implode(",", $this->post['qid']);
            $_ENV['question']->change_to_solve($qids);
            $this->onsearchquestion('问题状态设置成功!');
            exit;
        }
        $this->onsearchquestion();
    }
    //编辑回答内容
    function oneditanswercont() {
        if (isset($this->post['content'])) {
            $content = trim($this->post['content']);
            if ('' == $content) {
                $this->onsearchanswer('回答内容不能为空!', 'errormsg');
                exit;
            }
            $_ENV['answer']->update_content(intval($this->post['aid']), $content);
            $this->onsearchanswer('回答内容修改成功!');
        }
    }

    //删除回答
    function ondeleteanswer() {
        if (isset($this->post['aid'])) {
            $aids = implode(",", $this->post['aid']);
            $_ENV['answer']->remove($aids);
            $this->onsearchanswer('删除回答成功!');
            exit;
        }
    }

    function onaddtotopic() {
        $this->load("topic");
        if (isset($this->post['qids'])) {
            $_ENV['topic']->addtotopic($this->post['qids'], $this->post['topiclist']);
            $this->onsearchquestion('专题添加成功!');
        }
    }

    /* 问题审核 */

    function onexamine($msg='', $ty='') {
        $msg && $message = $msg;
        $ty && $type = $ty;
        @$page = max(1, intval($this->get[2]));
        $pagesize = 20;
        $startindex = ($page - 1) * $pagesize;
        $questionlist = $_ENV['question']->list_by_search(0, 0, 0, 0, 0,0, $startindex, $pagesize);
        $rownum = $_ENV['question']->rownum_by_search(0, 0, 0, 0, 0);
        $departstr = page($rownum, $pagesize, $page, "admin_question/examine");
        include template("verifyquestions", "admin");
    }

    /* 回答审核 */

    function onexamineanswer($msg='', $ty='') {
        $msg && $message = $msg;
        $ty && $type = $ty;
        @$page = max(1, intval($this->get[2]));
        $pagesize = 20;
        $startindex = ($page - 1) * $pagesize;
        $answerlist = $_ENV['answer']->list_by_condition('`status`=0', $startindex, $pagesize);
        $rownum = $this->db->fetch_total('answer', ' `status`=0');
        $departstr = page($rownum, $pagesize, $page, "admin_question/examineanswer");
        include template("verifyanswers", "admin");
    }
    
    function onmakeindex(){
        ignore_user_abort();
        set_time_limit(0);
        $_ENV['question']->makeindex();
        echo 'ok';
        exit;
    }

}

?>