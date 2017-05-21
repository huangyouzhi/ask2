<?php

!defined('IN_ASK2') && exit('Access Denied');

class doingmodel {

    var $db;
    var $base;
    var $actiontable = array(
        '1' => '提出了问题',
        '2' => '回答了该问题',
        '3' => '评论该回答',
        '4' => '关注了该问题',
        '5' => '赞同了该回答',
        '6' => '对该回答进行了追问',
        '7' => '继续回答了该问题',
        '8' => '采纳了回答',
        '9' => '发布了文章',
        '10' => '关注了专题',
        '11' => '关注了用户',
        '12' => '注册了网站',
        '13' => '收藏了文章',
        '14' => '评论了文章',
         '15' => '付费阅读了文章'
    );

    function doingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function add($authorid, $author, $action, $qid, $content = '', $referid = 0, $refer_authorid = 0, $refer_content = '') {
        $content && $content = strip_tags($content);
        $refer_content && $refer_content = strip_tags($refer_content);
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "doing(doingid,authorid,author,action,questionid,content,referid,refer_authorid,refer_content,createtime) VALUES (NULL,$authorid,'$author',$action,$qid,'$content',$referid,$refer_authorid,'$refer_content',{$this->base->time})");
    }
    function get_by_uid($uid, $loginstatus = 1) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
        $user['avatar'] = get_avatar_dir($uid);
        $user['register_time'] = tdate($user['regtime']);
        $user['lastlogin'] = tdate($user['lastlogin']);
         $is_followed = $this->is_followeduser( $user['uid'], $this->base->user['uid']);
       $user['hasfollower']=$is_followed==0 ? "0":"1";
      
        return $user;
    }

    /* 是否关注用户 */

    function is_followeduser($uid, $followerid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "user_attention WHERE uid=$uid AND followerid=$followerid");
    }
     /* 是否关注分类 */

    function is_followedcid($cid, $uid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "categotry_follower WHERE uid=$uid AND cid=$cid");
    }
    function gettopic($id) {
         $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE id='$id'");
        
    
        return $topic;
    }
    function get_cat_bycid($id) {
        $category= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "category WHERE id='$id'");
    $category['image']=get_cid_dir($category['id'],'small');
   $category['follow'] = $this->is_followedcid($category['id'], $this->base->user['uid']);
    $category['miaosu']=cutstr( checkwordsglobal(strip_tags( $category['miaosu'])), 140,'...');
        	 	$category['bigimage']=get_cid_dir($category['id'],'big');
        	 	return $category;
    }
    function list_by_type($searchtype = 'all', $uid = 0, $start = 0, $limit = 20) {
        $doinglist = array();
        //$sql = "SELECT q.title,q.attentions,q.answers,q.views,q.time,q.hidden,d.* FROM " . DB_TABLEPRE . "doing AS d," . DB_TABLEPRE . "question AS q WHERE q.id=d.questionid";
        //($searchtype == 'my') && $sql .= " AND d.authorid=$uid";
      //  ($searchtype == 'atentto') && $sql .=" AND q.id IN (SELECT qid FROM " . DB_TABLEPRE . "question_attention WHERE followerid=$uid)";
$sql="";
        switch ($searchtype){
        	case 'all':
        		 $sql.="select * from " . DB_TABLEPRE . "doing ";
        		break;
        		case 'my':
        			 $sql.="select * from " . DB_TABLEPRE . "doing where authorid=$uid ";
        		break;
        		case 'attento':
        			 $sql.="select * from " . DB_TABLEPRE . "doing where authorid=$uid and action in(4,10,11) ";
        		break;
        }
       
        $sql .=" ORDER BY createtime DESC LIMIT $start,$limit";
        //runlog('doing.txt', $sql);
        $query = $this->db->query($sql);
        while ($doing = $this->db->fetch_array($query)) {
          
            $doing['doing_time'] = tdate($doing['createtime']);
            $doing['user']=$this->get_by_uid($doing['authorid']);
            $doing['avatar'] = get_avatar_dir($doing['authorid']);
            $doing['actiondesc'] = $this->actiontable[$doing['action']];
            $doing['followerlist'] =$this->get_follower($doing['questionid']);
            if ($doing['refer_authorid']) {
                $doing['refer_avatar'] = get_avatar_dir($doing['refer_authorid']);
            }
             
               	
               
//                var $actiontable = array(
//        '1' => '提出了问题',
//        '2' => '回答了该问题',
//        '3' => '评论该回答',
//        '4' => '关注了该问题',
//        '5' => '赞同了该回答',
//        '6' => '对该回答进行了追问',
//        '7' => '继续回答了该问题',
//        '8' => '采纳了回答',
//        '9' => '发布了文章',
//        '10' => '关注了专题',
//        '11' => '关注了用户',
//        '12' => '注册了网站'
//    );
//  $question['url'] = url('question/view/' . $question['id'], $question['url']);
            switch ($doing['action']) {
            	case '1': //提出了问题
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		 $doing['content']=$doing['question']['title'];
            		 $doing['image']=getfirstimg($doing['question']['description']);
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '2'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '3':///
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '4'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	if($doing['question']==null){
            		  $doing['content']="该问题已被作者和管理员删除";
            		  $doing['url']= urlmap('category/viewtopic/hot', 2);
            	}
            	break;
            	case '5'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '6'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '7'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '8'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '9'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	case '10'://
            		$doing['category']=$this->get_cat_bycid($doing['questionid']);
            	$doing['url']= urlmap('category/view/' . $doing['questionid'], 2);
            	break;
            	case '11'://
            		 $doing['spaceuser']=$this->get_by_uid($doing['questionid']);
            	$doing['url']= urlmap('user/space/' . $doing['questionid'], 2);
            	break;
            	case '12'://
            	$doing['url']= urlmap('index' , 2);
            	break;
            	case '13'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		  $doing['content']=$doing['topic']['title'];
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	  	case '14'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		  $doing['content']=$doing['topic']['title'];
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	case '15'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		  $doing['content']=$doing['topic']['title'];
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	default:
            	$doing['url']= urlmap('index' , 2);
            	break;
            }
             $doing['url'] = url($doing['url']);
            $doinglist[] = $doing;
        }
        return $doinglist;
    }
function list_by_type_cache($searchtype = 'all', $uid = 0, $start = 0, $limit = 20) {
       $sql="";
        switch ($searchtype){
        	case 'all':
        		 $sql.="select * from " . DB_TABLEPRE . "doing ";
        		break;
        		case 'my':
        			 $sql.="select * from " . DB_TABLEPRE . "doing where authorid=$uid ";
        		break;
        		case 'attento':
        			 $sql.="select * from " . DB_TABLEPRE . "doing where authorid=$uid and action in(4,10,11) ";
        		break;
        }
       
        $sql .=" ORDER BY createtime DESC LIMIT $start,$limit";
        runlog('doing.txt', $sql);
        $query = $this->db->query($sql);
        while ($doing = $this->db->fetch_array($query)) {
          
            $doing['doing_time'] = tdate($doing['createtime']);
            $doing['user']=$this->get_by_uid($doing['authorid']);
            $doing['avatar'] = get_avatar_dir($doing['authorid']);
            $doing['actiondesc'] = $this->actiontable[$doing['action']];
            $doing['followerlist'] =$this->get_follower($doing['questionid']);
            if ($doing['refer_authorid']) {
                $doing['refer_avatar'] = get_avatar_dir($doing['refer_authorid']);
            }
             
               	
               
//                var $actiontable = array(
//        '1' => '提出了问题',
//        '2' => '回答了该问题',
//        '3' => '评论该回答',
//        '4' => '关注了该问题',
//        '5' => '赞同了该回答',
//        '6' => '对该回答进行了追问',
//        '7' => '继续回答了该问题',
//        '8' => '采纳了回答',
//        '9' => '发布了文章',
//        '10' => '关注了专题',
//        '11' => '关注了用户',
//        '12' => '注册了网站'
//    );
//  $question['url'] = url('question/view/' . $question['id'], $question['url']);
            switch ($doing['action']) {
            	case '1': //提出了问题
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		 $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '2'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '3':///
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		 $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '4'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		 $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '5'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '6'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '7'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '8'://
            		 $doing['question']=$this->getquestionbyqid($doing['questionid']);
            		  $doing['content']=$doing['question']['title'];
            		   $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['question']['description'])), 240,'...');
            	$doing['url']= urlmap('question/view/' . $doing['questionid'], 2);
            	break;
            	case '9'://
            		
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	case '10'://
            	$doing['url']= urlmap('category/view/' . $doing['questionid'], 2);
            	break;
            	case '11'://
            		$doing['spaceuser']=$this->get_by_uid($doing['questionid']);
            	$doing['url']= urlmap('user/space/' . $doing['questionid'], 2);
            	break;
            	case '12'://
            	$doing['url']= urlmap('index' , 2);
            	break;
            	case '13'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		  $doing['content']=$doing['topic']['title'];
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            		  	case '14'://
            		
            		$doing['topic']=$this->gettopic($doing['questionid']);
            		  $doing['content']=$doing['topic']['title'];
            		$doing['image']=getfirstimg($doing['topic']['describtion']);
            		  $doing['description']=cutstr( checkwordsglobal(strip_tags( $doing['topic']['describtion'])), 240,'...');
            	$doing['url']= urlmap('topic/getone/' . $doing['questionid'], 2);
            	break;
            	default:
            	$doing['url']= urlmap('index' , 2);
            	break;
            }
               $doing['url'] = url($doing['url']);
            $doinglist[] = $doing;
        }
        return $doinglist;
    }
    //删除动态
    function deletedoing($uid,$type,$typeid){
    	  $query = $this->db->query("DELETE  FROM " . DB_TABLEPRE . "doing WHERE authorid=$uid and action=$type and questionid=$typeid ");
    }
       /* 获取问题管理者列表信息 */

    function get_follower($qid, $start = 0, $limit = 16) {
        $followerlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "question_attention WHERE qid=$qid ORDER BY `time` DESC LIMIT $start,$limit");
        while ($follower = $this->db->fetch_array($query)) {
            $follower['avatar'] = get_avatar_dir($follower['followerid']);
            ;
            $followerlist[] = $follower;
        }
        return $followerlist;
    }
    

    function rownum_by_type($searchtype = 'all', $uid = 0) {
        $sql = "SELECT count(d.questionid) FROM " . DB_TABLEPRE . "doing AS d," . DB_TABLEPRE . "question AS q WHERE q.id=d.questionid";
        ($searchtype == 'my') && $sql .= " AND d.authorid=$uid";
        ($searchtype == 'atentto') && $sql .=" AND q.id IN (SELECT qid FROM " . DB_TABLEPRE . "question_attention WHERE followerid=$uid)";
        return $this->db->result_first($sql);
    }
    function getquestionbyqid($id) {
        $question = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "question WHERE id='$id'");
  
        return $question;
    }
    /**
     * 推荐关注用户
     */
    function recommend_user($limit = 6) {
        $this->base->load("user");
        $userlist = array();
        $usercount = $this->db->fetch_total("user", " 1=1");
        if ($usercount > 100) {
            $usercount = 101;
        }
        $start = rand(0, $usercount-1);
        $loginuid = $this->base->user['uid'];
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user  WHERE uid<>$loginuid AND uid NOT IN (SELECT uid FROM ".DB_TABLEPRE."user_attention WHERE followerid=$loginuid)  ORDER BY followers DESC,answers DESC,regtime DESC LIMIT $start,$limit ");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
             $user['is_follow'] = $this->is_followed($user['uid'],$loginuid);
            $user['category'] = $_ENV['user']->get_category($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }
  /* 是否关注问题 */

    function is_followed($uid, $followerid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "user_attention WHERE uid=$uid AND followerid=$followerid");
    }
}
