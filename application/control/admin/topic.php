<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_topiccontrol extends base {

    function admin_topiccontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load("topic");
        $this->load("topic_tag");
         $this->load("category");
    }

    function ondefault($msg='', $ty='') {
    	   $catetree = $_ENV['category']->get_categrory_tree($_ENV['category']->get_list());
    	if($this->post['submit']){
    		
    @$page = max(1, intval($this->get[5]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $srchtitle = isset($this->get[2]) ? urldecode($this->get[2]) : $this->post['srchtitle'];
        $srchauthor = isset($this->get[3]) ? urldecode($this->get[3]) : $this->post['srchauthor'];
     
        $srchcategory = isset($this->get[4]) ? $this->get[7] : $this->post['srchcategory'];
     
        $topiclist = $_ENV['topic']->list_by_search($srchtitle, $srchauthor,$srchcategory,$startindex, $pagesize);
       
        $rownum = $_ENV['topic']->rownum_by_search($srchtitle, $srchauthor, $srchcategory);
        $departstr = page($rownum, $pagesize, $page, "admin_topic/default/$srchtitle/$srchauthor/$srchcategory");
       
       
      
        $msg && $message = $msg;
        $ty && $type = $ty;
    
    	}else{
    		 @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('topic');
         $topiclist = $_ENV['topic']->get_list(2, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "admin_topic/default");
    	
    	}
  
    
        include template("topiclist", 'admin');
    }

    function onadd() {
        if (isset($this->post['submit'])) {
            $title = $this->post['title'];
            $desrc = $this->post['content'];
            $isphone= $this->post['isphone'];
            $topic_tag = $this->post['topic_tag'];
            $taglist = explode(",", $topic_tag);
            if($isphone=='on'){
            	$isphone=1;
            }else{
            	$isphone=0;
            }
              $acid = $this->post['topicclass'];
             
               if($acid==null)$acid=1;
            $imgname = strtolower($_FILES['image']['name']);
            if ('' == $title || '' == $desrc) {
                $this->ondefault('请完整填写专题相关参数!', 'errormsg');
                exit;
            }
            $type = substr(strrchr($imgname, '.'), 1);
            if (!isimage($type)) {
                $this->ondefault('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！', 'errormsg');
                exit;
            }
            $upload_tmp_file = ASK2_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;

            $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
            forcemkdir(ASK2_ROOT . '/data/attach/topic');
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                image_resize($upload_tmp_file, ASK2_ROOT . $filepath, 270, 220);

                $_ENV['topic']->add($title, $desrc, $filepath,$isphone,'1',$acid);
                $this->ondefault('添加成功！');
            } else {
                $this->ondefault('服务器忙，请稍后再试！');
            }
        } else {
            include template("addtopic", 'admin');
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
    	 if (isset($this->post['tid'])){
    	 	//SITE_URL.$suffix."q-$item[id]$fix
    	 	$tids=$this->post['tid'];
    	 	$q_size=count($tids);
    	 	for($i=0;$i<$q_size;$i++){
    	 		array_push($urls, SITE_URL.$suffix."article-".$tids[$i].$fix);
    	 	}
    	 }else{
    	 	 $this->ondefault('您还没选择推送文章!');
    	 }
    	if(trim($this->setting['baidu_api'])!=''&&$this->setting['baidu_api']!=null){
 
$api = $this->setting['baidu_api'];
$result=baidusend($api,$urls);
$this->ondefault('文章推送成功!');
    	}else{
    		 $this->ondefault('文章推送不成功，您还没设置百度推送的api地址，前往系统设置--seo设置里配置!');
    	}
    }
    /**
     * 后台修改专题
     */
    function onedit() {
        if (isset($this->post['submit'])) {
        	
            $title = $this->post['title'];
               $topic_tag = $this->post['topic_tag'];
            $taglist = explode(",", $topic_tag);
            $desrc = $this->post['content'];
            $tid = intval($this->post['id']);
            $upimg=$this->post['upimg'];
          $views=$this->post['views'];
        $isphone= $this->post['isphone'];
         $ispc= $this->post['ispc'];
            if($isphone=='on'){
            	$isphone=1;
            }else{
            	$isphone=0;
            }
          if($ispc=='on'){
            	$ispc=1;
            }else{
            	$ispc=0;
            }
             $acid = $this->post['topicclass'];
             
               if($acid==null)$acid=1;
            $imgname = strtolower($_FILES['image']['name']);
            if ('' == $title || '' == $desrc) {
                $this->ondefault('请完整填写专题相关参数!', 'errormsg');
                exit;
            }
                $topic = $_ENV['topic']->get($tid);
            if ($imgname) {
                $type = substr(strrchr($imgname, '.'), 1);
                if (!isimage($type)) {
                    $this->ondefault('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！', 'errormsg');
                    exit;
                }
                $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
                $upload_tmp_file = ASK2_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;
                forcemkdir(ASK2_ROOT . '/data/attach/topic');
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                    image_resize($upload_tmp_file, ASK2_ROOT . $filepath, 270, 220);
                    $_ENV['topic']->updatetopic($tid, $title, $desrc, $filepath,$isphone,$views,$acid,$ispc,$topic['price']);
                   // $this->ondefault('专题修改成功！');
                  $viewhref=urlmap('admin_topic/default',1);
                   $url=SITE_URL . $this->setting['seo_prefix'] . $viewhref . $this->setting['seo_suffix'];
                   header("Location:$url");
                } else {
                    $this->ondefault('服务器忙，请稍后再试！');
                }
            } else {
            	
                //$_ENV['topic']->updatetopic($tid, $title, $desrc,$upimg,$isphone,$views,$acid);
            	 $_ENV['topic']->updatetopic($tid, $title, $desrc, $filepath,$isphone,$views,$acid,$ispc,$topic['price']);
 $taglist && $_ENV['topic_tag']->multi_add(array_unique($taglist), $tid);
              //  $this->ondefault('专题修改成功！');
               $viewhref=urlmap('admin_topic/default',1);
                   $url=SITE_URL . $this->setting['seo_prefix'] . $viewhref . $this->setting['seo_suffix'];
                   header("Location:$url");
            }
        } else {
            $topic = $_ENV['topic']->get(intval($this->get[2]));
            
         $tagmodel=$_ENV['topic_tag']->get_by_aid($topic['id']);
         
        
         $topic['topic_tag']=implode(',', $tagmodel);
       
          $catmodel=$_ENV['category']->get($topic['articleclassid']);
               $categoryjs = $_ENV['category']->get_js();
            include template("addtopic", 'admin');
        }
    }

    //专题删除
    function onremove() {
        if (isset($this->post['tid'])) {
            $tids = implode(",", $this->post['tid']);
            $_ENV['topic']->remove($tids);
            $this->ondefault('专题删除成功！');
        }
    }

    /* 后台分类排序 */

    function onreorder() {
        $orders = explode(",", $this->post['order']);
        foreach ($orders as $order => $tid) {
            $_ENV['topic']->order_topic(intval($tid), $order);
        }
        $this->cache->remove('topic');
    }

    function onajaxgetselect() {
        echo $_ENV['topic']->get_select();
        exit;
    }
    
    function onmakeindex(){
        ignore_user_abort();
        set_time_limit(0);
        $_ENV['topic']->makeindex();
        echo 'ok';
        exit;
    }
}

?>