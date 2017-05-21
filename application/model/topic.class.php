<?php

!defined('IN_ASK2') && exit('Access Denied');

class topicmodel {

    var $db;
    var $base;
    var $index;
    var $search;
    function topicmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
     if ($this->base->setting['xunsearch_open']) {
            require_once $this->base->setting['xunsearch_sdk_file'];
            $xs = new XS('topic');
            $this->search = $xs->search;
            $this->index = $xs->index;
        }
    }

    /* 获取某个文章信息 */

    function get($id) {
         $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE id='$id'");
        
        if ($topic) {
            $topic['viewtime'] = tdate($topic['viewtime']);
            $topic['title'] = checkwordsglobal($topic['title']);
            $topic['artlen'] = strlen(strip_tags($topic['describtion']));
              $topic['describtion'] = checkwordsglobal($topic['describtion']);
        }
        return $topic;
    }
    //查看已经付费阅读的人
  function getreaduser($uid,$tid){
     
     	 $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic_viewhistory WHERE uid=$uid and tid=$tid ");
         return $topic;
     }
   function get_byname($title) {
         $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE title='$title'");
        
        if ($topic) {
            $topic['viewtime'] = tdate($topic['viewtime']);
             $topic['title'] = checkwordsglobal($topic['title']);
              $topic['describtion'] = checkwordsglobal($topic['describtion']);
            
        }
        return $topic;
    }
 function get_bylikename($word,$start=0, $limit=6){
    	$topiclist = array();
    	       if ($this->base->setting['xunsearch_open']) {
       
            $result = $this->search->setQuery($word)->setLimit($limit, $start)->search();
            foreach ($result as $doc) {
                $topic = array();
                $topic['id'] = $doc->id;
                $question['cid'] = $doc->articleclassid;
                $question['category_name'] = $this->base->category[$question['articleclassid']]['name'];
               
                $topic['author'] = $doc->author;
                $topic['authorid'] = $doc->authorid;
                 $topic['image'] =$topic->image;
                
                 $topic['title'] =$this->search->highlight($doc->title);
              $topic['describtion'] = $this->search->highlight($doc->describtion);
              $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
             $topic['describtion'] = highlight( cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...'),$word);
                $topic['format_time'] = tdate($topic['viewtime']);
            $topic['avatar']=get_avatar_dir($topic['authorid']);
           $topic['views'] = $doc->views;
            $topic['articles'] = $doc->articles;
             $topic['likes'] = $doc->likes;
              $topic['viewtime'] = tdate($doc->viewtime);
            $topiclist[] = $topic;
            }
			if(count($topiclist)==0){
				 $topiclist=$this->get_by_likename($word,$start, $limit);
			}
			
		
        } else {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic WHERE title like '%$word%' or describtion like '%$word%' order by id desc LIMIT $start,$limit");
       
      while ($topic = $this->db->fetch_array($query)) {
           $topic['title'] = checkwordsglobal($topic['title']);
              $topic['describtion'] = checkwordsglobal($topic['describtion']);
          $topic['title']=highlight( $topic['title'],$word);
	    
	      $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
              $topic['describtion'] = highlight( cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...'),$word);
                $topic['format_time'] = tdate($topic['viewtime']);
            $topic['avatar']=get_avatar_dir($topic['authorid']);
              $topic['viewtime'] = tdate($topic['viewtime']);
            $topiclist[] = $topic;
        }
        }
    	
       
        return $topiclist; 
    }
    function get_by_likename($word,$start=0, $limit=6){
    	$topiclist = array();
 
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic WHERE title like '%$word%' or describtion like '%$word%' order by id desc LIMIT $start,$limit");
       
      while ($topic = $this->db->fetch_array($query)) {
           $topic['title'] = checkwordsglobal($topic['title']);
              $topic['describtion'] = checkwordsglobal($topic['describtion']);
          $topic['title']=highlight( $topic['title'],$word);
	    
	      $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
               $topic['describtion'] = highlight( cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...'),$word);
                $topic['format_time'] = tdate($topic['viewtime']);
            $topic['avatar']=get_avatar_dir($topic['authorid']);
              $topic['viewtime'] = tdate($topic['viewtime']);
              
            $topiclist[] = $topic;
        }
        
    	
       
        return $topiclist; 
    }
 function rownum_by_user_article(){
    	$sql="SELECT COUNT(wz.authorid) as num, u.uid,u.username,u.signature,u.followers FROM `" . DB_TABLEPRE . "user` as u ," . DB_TABLEPRE . "topic as wz where u.uid=wz.authorid group by u.uid ORDER BY num DESC ";
    	 $query = $this->db->query($sql);
        return $this->db->num_rows($query);
    }

    /* 后台文章数目 */

    function rownum_by_search($title = '', $author = '', $cid = 0) {
   
       if ($this->base->setting['xunsearch_open']) {
            $rownum = $this->search->getLastCount();
            
            return $rownum;
        }else{
        	
                   $condition = " 1=1 ";
        $title && ($condition .= " AND `title` like '$title%' ");
        $author && ($condition .= " AND `author`='$author'");
        
        if ($cid) {
            $category = $this->base->category[$cid];
            $condition .= " AND `articleclassid"  . "`= $cid ";
        }
   
        return $this->db->fetch_total('topic', $condition);
   
        }
    }
  function get_user_articles($start = 0, $limit = 8){
    	$sql="SELECT COUNT(wz.authorid) as num, u.uid,u.username,u.signature,u.followers,u.answers FROM `" . DB_TABLEPRE . "user` as u ," . DB_TABLEPRE . "topic as wz where u.uid=wz.authorid group by u.uid ORDER BY num DESC LIMIT $start,$limit";
    	 $modellist = array();
        $query = $this->db->query($sql);
        while ($model = $this->db->fetch_array($query)) {
        	
        	 $model['avatar'] = get_avatar_dir($model['uid']);
            $is_followed = $this->is_followed( $model['uid'], $this->base->user['uid']);
                $model['hasfollower']=$is_followed==0 ? "0":"1";
            $modellist[] = $model;
        }
        return $modellist;
        
    }
    /* 是否关注问题 */

    function is_followed($uid, $followerid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "user_attention WHERE uid=$uid AND followerid=$followerid");
    }
    function get_article_by_uid($uid){
    	   	$sql="SELECT COUNT(t.id) as num ,c.name ,c.id ,t.authorid,u.username FROM `" . DB_TABLEPRE . "topic` as t ," . DB_TABLEPRE . "category as c," . DB_TABLEPRE . "user as u where c.id=t.articleclassid and t.authorid=$uid and t.authorid=u.uid GROUP BY t.articleclassid HAVING COUNT(t.id)>0 ORDER BY num DESC LIMIT 0,15";
    	 $modellist = array();
        $query = $this->db->query($sql);
        while ($model = $this->db->fetch_array($query)) {
        	
        	
           
            $modellist[] = $model;
        }
        return $modellist;
    }
    function get_bycatid($catid,$start=0, $limit=6,$questionsize=10){
    	 $topiclist = array();
    	   $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic where articleclassid in($catid) order by id desc LIMIT $start,$limit");
    	 while ($topic = $this->db->fetch_array($query)) {
          
           $topic['title'] = checkwordsglobal($topic['title']);
                $topic['avatar']=get_avatar_dir($topic['authorid']);
             $topic['describtion'] = cutstr(str_replace('&nbsp;', '', checkwordsglobal(strip_tags($topic['describtion']))) , 240,'...');
   
              $topic['description'] = cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...');
               $topic['answers']=$topic['articles'];
              $topic['format_time'] = tdate($topic['viewtime']);
              $topic['viewtime'] = tdate($topic['viewtime']);
                   $topic['attentions']=$topic['likes'];
            $topiclist[] = $topic;
        }
        return $topiclist;
    }
    function get_list($showquestion=0, $start=0, $limit=6,$questionsize=10) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic order by id desc LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
            ($showquestion == 1) && $topic['questionlist'] = $this->get_questions($topic['id'],0,$questionsize); //首页专题掉用
            ($showquestion == 2) && $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
            $topic['sortime'] = $topic['viewtime'];//用于排序
            $topic['format_time'] = tdate($topic['viewtime']);
            $topic['viewtime'] = tdate($topic['viewtime']);
            
             $topic['title'] = checkwordsglobal($topic['title']);
              $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
              $topic['describtion'] = cutstr(str_replace('&nbsp;', '', checkwordsglobal(strip_tags($topic['describtion']))) , 240,'...');
              $topic['description'] = cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...');
                 $topic['answers']=$topic['articles'];
                   $topic['attentions']=$topic['likes'];
            $topic['avatar']=get_avatar_dir($topic['authorid']);
            $topiclist[] = $topic;
        }
        return $topiclist;
    }
  function get_hotlist($showquestion=0, $start=0, $limit=6,$questionsize=10) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic where ispc=1 order by id desc LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
            ($showquestion == 1) && $topic['questionlist'] = $this->get_questions($topic['id'],0,$questionsize); //首页专题掉用
            ($showquestion == 2) && $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
            $topic['sortime'] = $topic['viewtime'];//用于排序
            $topic['format_time'] = tdate($topic['viewtime']);
            $topic['viewtime'] = tdate($topic['viewtime']);
           $topic['title'] = checkwordsglobal($topic['title']);
       $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
              $topic['describtion'] = cutstr(str_replace('&nbsp;', '', checkwordsglobal(strip_tags($topic['describtion']))) , 240,'...');
      
            $topic['avatar']=get_avatar_dir($topic['authorid']);
            $topiclist[] = $topic;
        }
        return $topiclist;
    }
    function rownum_by_tag($name) {
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "topic` AS q," . DB_TABLEPRE . "topic_tag AS t WHERE q.id=t.aid AND t.name='$name' ORDER BY q.views DESC");
        return $this->db->num_rows($query);
    }
  function rownum_by_title($word) {
         if ($this->base->setting['xunsearch_open']) {
            $rownum = $this->search->getLastCount();
        }else{
        	
               $rownum = $this->db->fetch_total('topic'," title like '%$word%' or describtion like '%$word%' ");
   
        }
        return $rownum;
    }
    function list_by_tag($name,  $start = 0, $limit = 20) {
        $toipiclist = array();
      
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "topic` AS q," . DB_TABLEPRE . "topic_tag AS t WHERE q.id=t.aid AND t.name='$name'  ORDER BY q.views  DESC LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
            $topic['category_name'] = $this->base->category[$topic['articleclassid']]['name'];
            $topic['format_time'] = tdate($topic['viewtime']);
            $topic['description'] =checkwordsglobal( strip_tags($topic['describtion']));
             $topic['title']=highlight(checkwordsglobal($topic['title']),$name);
	$topic['describtion']=highlight( $topic['describtion'],$name);
            $toipiclist[] = $topic;
        }
        return $toipiclist;
    }
 function get_list_byuid($uid, $start=0, $limit=6,$questionsize=10) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic where authorid=$uid order by id desc LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
           //$topic['describtion']= cutstr(strip_tags(str_replace('&nbsp;','',$topic['describtion'])),110,'...');
            $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
             $topic['format_time'] = tdate($topic['viewtime']);
              $topic['viewtime'] = tdate($topic['viewtime']);
               
           //$topic['image']=getfirstimg($topic['description']);
           $topic['avatar']=get_avatar_dir($topic['authorid']);
           $topic['describtion']=cutstr( checkwordsglobal(strip_tags($topic['describtion'])), 240,'...');
               $topiclist[] = $topic;
        }
        return $topiclist;
    }
    /* 后台文章搜索 */

    function list_by_search($title = '', $author = '', $cid = 0, $start = 0, $limit = 10) {
        $sql = "SELECT * FROM `" . DB_TABLEPRE . "topic` WHERE 1=1 ";
        $title && ($sql .= " AND `title` like '%$title%' ");
        $author && ($sql .= " AND `author`='$author'");
       
        if ($cid) {
            $category = $this->base->category[$cid];
            $sql .= " AND `articleclassid"  . "`= $cid ";
        }
      
        $sql.=" ORDER BY `viewtime` DESC LIMIT $start,$limit";
        $topiclist = array();
      
               if ($this->base->setting['xunsearch_open']) {
       
            $result = $this->search->setQuery($title)->setLimit($limit, $start)->search();
            foreach ($result as $doc) {
                $topic = array();
                $topic['id'] = $doc->id;
                $question['cid'] = $doc->articleclassid;
                $question['category_name'] = $this->base->category[$question['articleclassid']]['name'];
               
                $topic['author'] = $doc->author;
                $topic['authorid'] = $doc->authorid;
                 $topic['image'] =$topic->image;
                
                 $topic['title'] =$this->search->highlight($doc->title);
              $topic['describtion'] = $this->search->highlight($doc->describtion);
           $topic['views'] = $doc->views;
            $topic['articles'] = $doc->articles;
             $topic['likes'] = $doc->likes;
              $topic['viewtime'] = tdate($doc->viewtime);
            $topiclist[] = $topic;
            }
			if(count($topiclist)==0){
			
				 $topiclist=$this->list_by_search2($title , $author, $cid, $start, $limit);
			}
			
		
        }else{
         $query = $this->db->query($sql);
        while ($topic = $this->db->fetch_array($query)) {
             $topic['describtion']= cutstr(strip_tags(str_replace('&nbsp;','',$topic['describtion'])),110,'...');
            $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
              $topic['viewtime'] = tdate($topic['viewtime']);
            $topiclist[] = $topic;
        }
        } 
        
       
        return $topiclist;
    }
 function list_by_search2($title = '', $author = '', $cid = 0, $start = 0, $limit = 10) {
        $sql = "SELECT * FROM `" . DB_TABLEPRE . "topic` WHERE 1=1 ";
        $title && ($sql .= " AND `title` like '%$title%' ");
        $author && ($sql .= " AND `author`='$author'");
       
        if ($cid) {
            $category = $this->base->category[$cid];
            $sql .= " AND `articleclassid"  . "`= $cid ";
        }
      
        $sql.=" ORDER BY `viewtime` DESC LIMIT $start,$limit";
        $topiclist = array();
      
         $query = $this->db->query($sql);
        while ($topic = $this->db->fetch_array($query)) {
             $topic['describtion']= cutstr(strip_tags(str_replace('&nbsp;','',$topic['describtion'])),110,'...');
            $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
              $topic['viewtime'] = tdate($topic['viewtime']);
            $topiclist[] = $topic;
        }
        
        
       
        return $topiclist;
    }
 function get_list_bycidanduid($cid,$uid, $start=0, $limit=6) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic where authorid=$uid and articleclassid=$cid order by viewtime  desc LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
          
        $topic['describtion']= cutstr(strip_tags(str_replace('&nbsp;','',$topic['describtion'])),110,'...');
              $topic['viewtime'] = tdate($topic['viewtime']);
            $topiclist[] = $topic;
        }
        return $topiclist;
    }
    
    function get_questions($id, $start=0, $limit=5) {
        $questionlist = array();
        $query = $this->db->query("SELECT q.title,q.id FROM " . DB_TABLEPRE . "tid_qid as t," . DB_TABLEPRE . "question as q WHERE t.qid=q.id AND t.tid=$id LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
        	 $question['title']=checkwordsglobal( $question['title']);
            
            $questionlist[] = $question;
        }
        return $questionlist;
    }
    function get_list_bywhere($showquestion,$questionsize) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic where isphone='1' order by displayorder asc ");
        while ($topic = $this->db->fetch_array($query)) {
            ($showquestion == 1) && $topic['questionlist'] = $this->get_questions($topic['id'],0,$questionsize); //首页专题掉用
            ($showquestion == 2) && $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
  $topic['viewtime'] = tdate($topic['viewtime']);
   $topic['title'] = checkwordsglobal($topic['title']);
        $topic['describtion'] = checkwordsglobal($topic['describtion']);
            $topiclist[] = $topic;
            
           
        }
        return $topiclist;
    }
    function get_select() {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic   LIMIT 0,50");
        $select = '<select name="topiclist">';
        while ($topic = $this->db->fetch_array($query)) {
            $select .= '<option value="' . $topic['id'] . '">' . $topic['title'] . '</option>';
        }
        $select .='</select>';
        return $select;
    }
   
    /* 后台管理编辑专题 */

    function update($id, $title, $desrc, $filepath='') {
        if ($filepath)
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`describtion`='$desrc' , `image`='$filepath'  WHERE `id`=$id");
        else
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`describtion`='$desrc'  WHERE `id`=$id");
        if ($this->base->setting['xunsearch_open']) {
            $topic = array();
            $topic['id'] = $id;
           
            $topic['image'] = $filepath;
            $topic['title'] = $title;
            $topic['describtion'] = $desrc;
            $doc = new XSDocument;
            $doc->setFields($topic);
            $this->index->update($doc);
        }
    
    
    }
function updatetopicbk($id, $title, $desrc, $filepath='',$isphone='',$views='',$cid,$price) {
	$creattime = $this->base->time;
 if ($filepath)
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`price`='$price' ,`describtion`='$desrc' , `image`='$filepath', `isphone`='$isphone', `views`='$views' ,`articleclassid`='$cid',`viewtime`='$creattime' WHERE `id`=$id");
        else
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`price`='$price' ,`describtion`='$desrc',`isphone`='$isphone', `views`='$views' ,`articleclassid`='$cid',`viewtime`='$creattime' WHERE `id`=$id");
    if ($this->base->setting['xunsearch_open']) {
            $topic = array();
            $topic['id'] = $id;
            $topic['views'] = $views;
             $topic['articleclassid'] = $cid;
              $topic['viewtime'] = $creattime;
            $topic['image'] = $filepath;
            $topic['title'] = $title;
            $topic['describtion'] = $desrc;
            $doc = new XSDocument;
            $doc->setFields($topic);
            $this->index->update($doc);
        }

}
function updatetopic($id, $title, $desrc, $filepath='',$isphone='',$views='',$cid,$ispc=0,$price) {
 if ($filepath)
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title',`price`='$price'  ,`describtion`='$desrc' , `image`='$filepath', `isphone`='$isphone', `ispc`='$ispc', `views`='$views' ,`articleclassid`='$cid' WHERE `id`=$id");
        else
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title',`price`='$price'  ,`describtion`='$desrc',`isphone`='$isphone', `ispc`='$ispc', `views`='$views' ,`articleclassid`='$cid' WHERE `id`=$id");

   if ($this->base->setting['xunsearch_open']) {
            $topic = array();
            $topic['id'] = $id;
            $topic['views'] = $views;
             $topic['articleclassid'] = $cid;
             
              if ($filepath){
              	$topic['image'] = $filepath;
              }
            
            $topic['title'] = $title;
            $topic['describtion'] = $desrc;
            $doc = new XSDocument;
            $doc->setFields($topic);
            $this->index->update($doc);
        }

}
function updatetopichot($id,$ispc=0) {
 $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `ispc`='$ispc' WHERE `id`=$id");
           
    }
function addtopicviewhistory($uid, $username,$tid) {
    	$creattime = $this->base->time;
        $this->db->query("INSERT INTO `" . DB_TABLEPRE . "topic_viewhistory`(`uid`,`username`,`tid`,`time`) VALUES ('$uid','$username','$tid',$creattime)");
  $id = $this->db->insert_id();

 return $id;
 }
   /* 后台添加专题 */

    function add($title, $desc, $image,$isphone='0',$views='1',$cid=1) {
    	$creattime = $this->base->time;
    	$author=$this->base->user['username'];
    	$authorid=$this->base->user['uid'];
    	//exit("INSERT INTO `" . DB_TABLEPRE . "topic`(`title`,`describtion`,`image`,`isphone`) VALUES ('$title','$desc','$image','$isphone')");
        $this->db->query("INSERT INTO `" . DB_TABLEPRE . "topic`(`title`,`describtion`,`image`,`author`,`authorid`,`isphone`,`views`,`articleclassid`) VALUES ('$title','$desc','$image','$author','$authorid','$isphone','$views','$cid')");
         $aid = $this->db->insert_id();
        if ($this->base->setting['xunsearch_open'] && $aid) {
            $topic = array();
            $topic['id'] = $aid;
            $topic['views'] = $views;
             $topic['articles'] = 0;
              $topic['likes'] = 0;
            $topic['articleclassid'] = $cid;
            $topic['title'] = checkwordsglobal( $title);
            $topic['describtion'] =checkwordsglobal($desc) ;
            $topic['author'] = $author;
            $topic['authorid'] = $authorid;
            $topic['viewtime'] = $creattime;

            $doc = new XSDocument;
            $doc->setFields($topic);
            $this->index->add($doc);
        }
     return $aid;
    }
 function addtopic($title, $desc, $image,$author,$authorid,$views,$articleclassid,$price=0) {
    	$creattime = $this->base->time;
        $this->db->query("INSERT INTO `" . DB_TABLEPRE . "topic`(`title`,`describtion`,`image`,`author`,`authorid`,`views`,`articleclassid`,`viewtime`,`price`) VALUES ('$title','$desc','$image','$author','$authorid','$views','$articleclassid','$creattime',$price)");
  $aid = $this->db->insert_id();
   if ($this->base->setting['xunsearch_open'] && $aid) {
            $topic = array();
            $topic['id'] = $aid;
            $topic['views'] = $views;
            $topic['articles'] = 0;
            $topic['likes'] = 0;
            $topic['articleclassid'] = $articleclassid;
            $topic['title'] = checkwordsglobal( $title);
            $topic['describtion'] =checkwordsglobal($desc) ;
            $topic['author'] = $author;
            // $topic['price'] = $price;
            $topic['authorid'] = $authorid;
            $topic['viewtime'] = $creattime;

            $doc = new XSDocument;
            $doc->setFields($topic);
            $this->index->add($doc);
        }
 return $aid;
 }
    function addtotopic($qids, $tid) {
        $qidlist = explode(",", $qids);
        $sql = "INSERT INTO " . DB_TABLEPRE . "tid_qid (`tid`,`qid`) VALUES ";
        foreach ($qidlist as $qid) {
            $sql .=" ($tid,$qid),";
        }
        $this->db->query(substr($sql, 0, -1));
    }

    /* 后台管理删除分类 */

    function remove($tids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "topic` WHERE `id` IN  ($tids)");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "tid_qid` WHERE `tid` IN ($tids)");
        
     if ($this->base->setting['xunsearch_open']) {
            $this->index->del(explode(",", $tids));
        }
    }

    /* 后台管理移动分类顺序 */

    function order_topic($id, $order) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET `displayorder` = '{$order}' WHERE `id` = '{$id}'");
    }

    /*创建文章索引*/
       function makeindex() {
        if ($this->base->setting['xunsearch_open']) {
            $this->index->clean();
            $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic ");
            while ($topic = $this->db->fetch_array($query)) {
                $data = array();
                $data['id'] = $topic['id'];
                $data['articleclassid'] = $topic['articleclassid'];
              $data['image'] = $topic['image'];
                $data['author'] = $topic['author'];
                $data['authorid'] = $topic['authorid'];
                $data['views'] = $topic['views'];
                $data['articles'] = $topic['articles'];
                $data['likes'] = $topic['likes'];
                $data['viewtime'] = $topic['viewtime'];
           
                $data['title'] = $topic['title'];
                $data['describtion'] = $topic['describtion'];
                $doc = new XSDocument;
                $doc->setFields($data);
                $this->index->add($doc);
            }
        }
    }
    
}

?>
