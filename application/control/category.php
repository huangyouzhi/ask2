<?php

!defined('IN_ASK2') && exit('Access Denied');

class categorycontrol extends base {
var $whitelist;
    function categorycontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load('category');
        $this->load('question');
         $this->load("topic");
          $this->whitelist="attentto,search,viewtopic";
    }

    function onviewtopic(){
    	 $navtitle = "热门专题" ;
    	 $status = isset($this->get[2]) ? $this->get[2] : 'hot';
        @$page = max(1, intval($this->get[3]));
        $pagesize = 21;
        $startindex = ($page - 1) * $pagesize;
          $rownum = $this->db->fetch_total('category', " 1=1 ");
        
            $catlist = $_ENV['category']->listtopic($status,$startindex, $pagesize);
              $departstr = page($rownum, $pagesize, $page, "category/viewtopic/$status");
        include template('category_all');
    }
    //category/view/1/2/10
    //cid，status,第几页？
    function onview() {
        $this->load("expert");
        $cid = intval($this->get[2])?$this->get[2]:'all';
        $status = isset($this->get[3]) ? $this->get[3] : 'all';
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        if ($cid != 'all') {
            $category = $this->category[$cid]; //得到分类信息
            $navtitle = $category['name'];
            $cfield = 'cid' . $category['grade'];
        } else {
            $category = $this->category;
            $navtitle = '全部分类';
            $cfield = '';
            $category['pid'] = 0;
        }
          if ($cid != 'all') {
          	 $category=$_ENV['category']->get($cid);
          }
        
        $statusword="";
        switch ($status){
        	case '1':
        		$statusword='待解决';
        		break;
        		case '2':
        			$statusword='已解决';
        		break;
        		case '4':
        			$statusword='高悬赏';
        		break;
        		case '6':
        			$statusword='推荐';
        		break;
        		case 'all':
        			$statusword='全部';
        			break;
        }
               $is_followed = $_ENV['category']->is_followed($cid, $this->user['uid']);
        $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status($cfield, $cid, $status); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status($cfield, $cid, $status, $startindex, $pagesize); //问题列表数据
  $topiclist = $_ENV['topic']->get_bycatid($cid, 0, 8);
        $followerlist=$_ENV['category']->get_followers($cid,0,8); //获取导航
        $departstr = page($rownum, $pagesize, $page, "category/view/$cid/$status"); //得到分页字符串
        $navlist = $_ENV['category']->get_navigation($cid); //获取导航
        $sublist = $_ENV['category']->list_by_cid_pid($cid, $category['pid']); //获取子分类
        $expertlist = $_ENV['expert']->get_by_cid($cid); //分类专家
        
      $trownum = $this->db->fetch_total('topic',"articleclassid in($cid)");
        $seo_description="";
        $seo_keywords="";
      
        if($category['alias']){
        	$navtitle=$category['alias'];
        }
       
       
        include template('category');
    }


 
    function onsearch(){
    		

               $hidefooter='hidefooter';
        $type="category";
        $word =urldecode($this->get[2]);
        $word = str_replace(array("\\","'"," ","/","&"),"", $word);
        $word = strip_tags($word);
        $word = htmlspecialchars($word);
        $word = taddslashes($word, 1);
        (!$word) && $this->message("搜索关键词不能为空!", 'BACK');
        $navtitle = $word ;
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
          $seo_description=$word;
     $seo_keywords= $word;
             $rownum = $this->db->fetch_total('category', " `name` like '%$word%' ");
         $catlist = $_ENV['category']->list_by_name($word, $startindex, $pagesize);
     
         $departstr = page($rownum, $pagesize, $page, "category/search/$word");
        include template('serach_category');
    }
    function onlist() {
        $status = isset($this->get[2]) ? $this->get[2] : 'all';
        $navtitle = $statustitle = $this->statusarray[$status];
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
      
        $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status('', 0, $status); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status('', 0, $status, $startindex, $pagesize); //问题列表数据
        $departstr = page($rownum, $pagesize, $page, "category/list/$status"); //得到分页字符串
        $metakeywords = $navtitle;
        $metadescription = '问题列表' . $navtitle;
        include template('list');
    }

    function onrecommend() {
        $this->load('topic');
        $navtitle = '专题列表';
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('topic');
        $topiclist = $_ENV['topic']->get_list(2,$startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "category/recommend");
        $metakeywords = $navtitle;
        $metadescription = '精彩推荐列表';
        include template('recommendlist');
    }
    function onattentto(){
    	       $cid = intval($this->post['cid']);
        if (!$cid) {
            exit('error');
        }
        if($this->user['uid']==0){
        	exit("-1");
        }
        $is_followed = $_ENV['category']->is_followed($cid, $this->user['uid']);
        if ($is_followed) {
            $_ENV['category']->unfollow($cid, $this->user['uid']);
             $this->load("doing");
             $_ENV['doing']->deletedoing($this->user['uid'],10,$cid);
        } else {
        	 $this->load("doing");
        	  $category = $this->category[$cid]; //得到分类信息
             $_ENV['doing']->add($this->user['uid'], $this->user['username'], 10, $cid, $category['name']);
            $_ENV['category']->follow($cid, $this->user['uid']);
           
        }
        exit('ok');
    }

}

?>