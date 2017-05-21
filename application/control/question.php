<?php

!defined('IN_ASK2') && exit('Access Denied');

//0、未审核 1、待解决、2、已解决 4、悬赏的 9、 已关闭问题

class questioncontrol extends base {

	private $serach_num='';
	var $whitelist;
    function questioncontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("question");
        $this->load("category");
        $this->load("answer");
        $this->load("expert");
        $this->load("tag");
        $this->load("topic_tag");
        $this->load("user");
        $this->load("userlog");
        $this->load("doing");
          $this->load("topic");
          $this->serach_num=isset($this->setting['search_shownum']) ? $this->setting['search_shownum']:'5';
 $this->whitelist="delete,postmedia,voice,postanswerreward,deleteanswer";
   
    }

  
 /* 提交回答 */
 function onajaxanswer() {
 	 $message=array();
        //只允许专家回答问题
        if (isset($this->setting['allow_expert']) && $this->setting['allow_expert'] && !$this->user['expert']) {
         
           $message['message']='站点已设置为只允许专家回答问题，如有疑问请联系站长.';
           echo json_encode($message);
             exit();
        }
     if ($this->user['uid'] ==0) {
           
            
         
              $message['message']='游客先登录在回答！';
               echo json_encode($message);
                                   exit();
        }
        $qid = $this->post['qid'];
       
        $question = $_ENV['question']->get($qid);
        if (!$question) {
            
          
            $message['message']='提交回答失败,问题不存在!';
             echo json_encode($message);
             exit();
        }
           	$useragent = $_SERVER['HTTP_USER_AGENT']; 
  
      

 	if(!strstr($useragent, 'MicroMessenger')&&isset($this->setting['code_ask'])&&$this->setting['code_ask']=='1'&&$this->user['credit1']<$this->setting['jingyan']&&$this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            
             $message['message']="验证码错误!";
        					    echo json_encode($message);
                                   exit();
        }
        				}
        				
       	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1&&$this->user['groupid']!=1){
        				
        					
        					   $message['message']="必须激活邮箱才能回复!";
        					    echo json_encode($message);
                                   exit();
        				}
        			}
        if ($this->user['uid'] == $question['authorid']) {
           
            
         
              $message['message']='提交回答失败，不能自问自答！';
               echo json_encode($message);
                                   exit();
        }
        //$this->setting['code_ask'] && $this->checkcode(); //检查验证码
        $already = $_ENV['question']->already($qid, $this->user['uid']);
        
        if($already){
        	 $message['message']='不能重复回答同一个问题，可以修改自己的回答！';
               echo json_encode($message);
                                   exit();
        	
        }
         	//老子故意让你这种发广告的验证完所有信息，最后告诉你丫的进入网站黑名单不能回答			
    	if($this->user['isblack']==1){
   
         $message['message']="黑名单用户无法回答问题!";
           echo json_encode($message);
             exit();
        	}
      
        $title = $this->post['title'];
          $chakanjine =doubleval( $this->post['chakanjine']);
        $content = $this->post['content'];
        //检查审核和内容外部URL过滤
        $status = intval(2 != (2 & $this->setting['verify_question']));
        $allow = $this->setting['allow_outer'];
        if (3 != $allow && has_outer($content)) {
        
            
            if( 0 == $allow ){
            	$message['message']='内容包含外部链接，发布失败!';
               echo json_encode($message);
                                   exit();
            }
            1 == $allow && $status = 0;
            2 == $allow && $content = filter_outer($content);
            
            
        }
        //检查违禁词
        $contentarray = checkwords($content);
        1 == $contentarray[0] && $status = 0;
      
        
        if(2 == $contentarray[0]){
        	$message['message']='内容包含非法关键词，发布失败!';
               echo json_encode($message);
                                   exit();
        }
        $content = $contentarray[1];

        /* 检查提问数是否超过组设置 */
        if($this->user['answerlimits'] && ($_ENV['userlog']->rownum_by_time('answer') >= $this->user['answerlimits'])) 
        
        {
        	
        	 
        	 $message['message']="你已超过每小时最大回答数" . $this->user['answerlimits'] . ',请稍后再试！';
               echo json_encode($message);
                                   exit();
        }
        
       
                


$content_temp=str_replace('<p>', '', $content);
$content_temp=str_replace('</p>', '', $content_temp);
$content_temp=str_replace('&nbsp;', '', $content_temp);
$content_temp= preg_replace("/\s+/",'',$content_temp);
$content_temp = preg_replace('/s(?=s)/', '', $content_temp);
$content_temp=trim($content_temp);
         if(trim($content_temp)==''){
         
         	$message['message']='回答不能为空！';
               echo json_encode($message);
                                   exit();
         }
         if($this->user['groupid']==1){
         	$status=2;
         }
         	
         	
         
        $_ENV['answer']->add($qid, $title, $content, $status,$chakanjine);
        //回答问题，添加积分
        $this->credit($this->user['uid'], $this->setting['credit1_answer'], $this->setting['credit2_answer']);
        //给提问者发送通知
        $this->send($question['authorid'], $question['id'], 0);
     
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['userlog']->add('answer');
        $_ENV['doing']->add($this->user['uid'], $this->user['username'], 2, $qid, $content);
        if (0 == $status) {
        
        	$message['message']='提交回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......';
               echo json_encode($message);
                                   exit();
           
        } else {
        	$quser= $_ENV['user']->get_by_uid($question['authorid']);
        	 global $setting;
        	$mpurl = SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
        	 //发送邮件通知
            $subject = "问题有新回答！" ;
            $emailmessage = $content.'<p>现在您可以点击<a swaped="true" target="_blank" href="' . $mpurl . '">查看最新回复</a>。</p>';
                 if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$quser['active']==1){
                   
                 	sendmail($quser, $subject, $emailmessage);
                 }
               
         
            $message['emal']='1';
            $message['message']='ok';
               echo json_encode($message);
                                   exit();
                                   
        }
    }
  

    /* 提交问题 */

    function onadd() {
 
    	 	$useragent = $_SERVER['HTTP_USER_AGENT']; 

      $iswxbrower=false;
if (strstr($useragent, 'MicroMessenger')) {
	$iswxbrower=true;
}
        $navtitle = "提出问题";
        
        	 
    	
            if (0 == $this->user['uid']) {
                $this->setting["ucenter_open"] && $this->message("UCenter开启后游客不能提问!", 'BACK');
            }
            $categoryjs = $_ENV['category']->get_js();
          
            $askfromuid = intval($this->get['2']);
            if ($askfromuid){
            
            	
            	 	
            	$touser = $_ENV['user']->get_by_uid($askfromuid);
            	
            	
            	if($touser['uid']==$this->user['uid']){
            		$this->message("不能对自己提问!", 'BACK');
            	}
            }else{
            	
            	// $_SESSION["asksid"]= '==========='.getRandChar(56);
            }
                
      
            if(is_mobile()){
            	$catetree = $_ENV['category']->get_categrory_tree($_ENV['category']->get_list());
            }
           
      
            include template('ask');
     
    }
    function onajaxgetcat(){
      $msg=array();
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
       
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
                $msg['message'] ='error';
                echo json_encode($msg);
             exit();
            }
           
             $msg['message'] ='ok';  
             $msg['cid'] =$cid;
                $msg['cid1'] =$cid1;
                 $msg['cid2'] =$cid2;
                  $msg['cid3'] =$cid3;
                  
            echo json_encode($msg);
             exit();
        
    }
    }
    function onajaxadd(){
    	 	$useragent = $_SERVER['HTTP_USER_AGENT']; 

      $iswxbrower=false;
if (strstr($useragent, 'MicroMessenger')) {
	$iswxbrower=true;
}
     $message=array();
      if ($this->user['uid'] ==0) {
           
            
         
              $message['message']='游客先登录在回答！';
               echo json_encode($message);
                                   exit();
        }
        
        			if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        					$viewhref=urlmap('user/editemail',1);
        					  
        					   
        					    $message['message']="必须激活邮箱才能提问!";
           echo json_encode($message);
             exit();
        				}
        			}
        			
        			if(!$iswxbrower){
        				if(isset($this->setting['code_ask'])&&$this->setting['code_ask']=='1'&&$this->user['credit1']<$this->setting['jingyan']){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
           
            		    $message['message']="验证码错误!";
           echo json_encode($message);
             exit();
        }
        				}
        			}
        			
        				
        	//老子故意让你这种发广告的验证完所有信息，最后告诉你丫的进入网站黑名单不能提问			
    	if($this->user['isblack']==1){
   
         $message['message']="黑名单用户无法发布问题!";
           echo json_encode($message);
             exit();
        	}
      
        
            $title = htmlspecialchars($this->post['title']);
            if($title==''){
            	
            	    $message['message']="标题不能为空!";
           echo json_encode($message);
             exit();
             
            }
           $q= $_ENV['question']->get_by_title(htmlspecialchars($title));
    	 	 if($q!=null)
    	 	 {
    	 	 	   $viewurl = urlmap('question/view/' . $q['id'], 2);

      	$mpurl = SITE_URL . $this->setting['seo_prefix'] . $viewurl.$this->setting['seo_suffix'];
    	 	 	 $message['url']="$mpurl";
    	 	 	    $message['message']="已有同样问题存在!";
               echo json_encode($message);
             exit();
    	 	 }
          //  $description = strip_tags($this->post['description']);
            $description = $this->post['description'];
            $cid1 = intval($this->post['cid1']);
            $cid2 =intval( $this->post['cid2']);
            $cid3 =intval( $this->post['cid3']);
            $cid =intval($this->post['cid']);
            
            $hidanswer = intval($this->post['hidanswer']) ? 1 : 0;
            $price = abs($this->post['givescore']);
              $jine = floatval($this->post['jine']);
            $askfromuid =intval( $this->post['askfromuid']);
         $needpay=0;
    	$touser = $_ENV['user']->get_by_uid($askfromuid);
            	
            	if($touser!=null){
            		if($touser['uid']==$this->user['uid']){
            		
            		
            		 $message['message']="不能对自己提问!";
           echo json_encode($message);
             exit();
            	}
            	$needpay=$touser['mypay'];
            	}
            	
            
            	
            $offerscore = $price;
            ($hidanswer) && $offerscore+=10;
    if($jine==0.1){
            	    $message['message']="太扣了，金额不能小于0.2元";
           echo json_encode($message);
             exit();
            }
       if($jine>200){
            	    $message['message']="金额不能大于200";
           echo json_encode($message);
             exit();
            }
            $tmjine=($jine+$needpay)*100;
            if($this->user['jine']<$tmjine){
            	 $message['message']="您在平台账户钱包金额不够，请充值在提问";
           echo json_encode($message);
             exit();
            }
         $shangjin=$jine;
         
         if($hidanswer==1){
            	if (intval($this->user['credit2']) < $offerscore) {
            	
            		    $message['message']="匿名发布财富值不够!匿名时会多消耗10财富值";
           echo json_encode($message);
             exit();
            	}
            }else{
            	 if(intval($this->user['credit2']) < $offerscore) {
            	 
            	 	    $message['message']="财富值不够!";
           echo json_encode($message);
             exit();
            	 }
            }
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($description)) {
               if( 0 == $allow){
      
          $message['message']="内容包含外部链接，发布失败!";
           echo json_encode($message);
             exit();
               }
                1 == $allow && $status = 0;
                2 == $allow && $description = filter_outer($description);
            }
            //检查标题违禁词
            $contentarray = checkwords($title);
            1 == $contentarray[0] && $status = 0;
            if(2 == $contentarray[0] ){
            
            	
          $message['message']="问题包含非法关键词，发布失败!";
           echo json_encode($message);
             exit();
            }
            $title = $contentarray[1];

            //检查问题描述违禁词
            $descarray = checkwords($description);
            1 == $descarray[0] && $status = 0;
            if(2 == $descarray[0] ){
            
            	$message['message']="问题描述包含非法关键词，发布失败!";
           echo json_encode($message);
             exit();
            }
            $description = $descarray[1];

            /* 检查提问数是否超过组设置 */
            if($this->user['questionlimits'] && ($_ENV['userlog']->rownum_by_time('ask') >= $this->user['questionlimits'])){

            		$message['message']="你已超过每小时最大提问数" . $this->user['questionlimits'] . ',请稍后再试！';
           echo json_encode($message);
             exit();
            }
                  
           if($this->user['groupid']==1){
         	$status=1;
         }
            $qid = $_ENV['question']->add($title, $description, $hidanswer, $price, $cid, $cid1, $cid2, $cid3, $status,$shangjin,$askfromuid);

        
            
            $_ENV['user']->follow($qid, $this->user['uid'], $this->user['username']);
            $taglist=dz_segment(htmlspecialchars($title));
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
            //增加用户积分，扣除用户悬赏的财富
            if ($this->user['uid']) {
                $this->credit($this->user['uid'], 0, -$offerscore, 0, 'offer');
                $this->credit($this->user['uid'], $this->setting['credit1_ask'], $this->setting['credit2_ask']);
            }
            $viewurl = urlmap('question/view/' . $qid, 2);
            /* 如果是向别人提问，则需要发个消息给别人 */
            if ($askfromuid) {
                $this->load("message");
               
              
                $username = addslashes($this->user['username']);
                $_ENV['message']->add($username, $this->user['uid'], $touser['uid'], '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
                
                
                if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$touser['active']==1){
                	sendmail($touser, '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
                }
                
            }
            //如果ucenter开启，则postfeed
            if ($this->setting["ucenter_open"] && $this->setting["ucenter_ask"]) {
                $this->load('ucenter');
                $_ENV['ucenter']->ask_feed($qid, $title, $description);
            }
            $_ENV['userlog']->add('ask');
            $_ENV['doing']->add($this->user['uid'], $this->user['username'], 1, $qid, $description);
  
        
        
            if (0 == $status) {
         
             $message['url']= SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'];
             $message['sh']=1;
            $message['message']='ok';
           echo json_encode($message);
             exit();
            
            } else {
            	 $this->load("message");
            	 $this->load("user");
            	$touser = $_ENV['user']->get_by_uid(1);
              
            	if($touser){
            	 if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$touser==1){
                sendmail($touser, '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
            	     }
            	}
            	  
            	     
            	    // exit('ddddddddd11dddddd');
                 //$username = addslashes($this->user['username']);
             
            
                //改相关分类专家私信
              $expert1=$this->sendmessagetoexpert($cid);
                 $expert2=$this->sendmessagetoexpert($cid1);
                  $expert3=$this->sendmessagetoexpert($cid2);
                   $expert4=$this->sendmessagetoexpert($cid3);
                   
                   $result=array_merge($expert1,$expert2,$expert3,$expert4);
                    $result=array_unique($result);
             
                    foreach ($result as $key=>$val){
                    
             if($this->user['uid']!=$val['uid'])
               $_ENV['message']->add($username, $this->user['uid'], $val['uid'], '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
            
                    }
              if(isset( $_SESSION["asksid"])){
            	unset($_SESSION["asksid"]);  
            }
                  $message['url']= SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'];
                     $message['message']="ok";
           echo json_encode($message);
             exit();
            }
    }
    function sendmessagetoexpert($cid){
    	$expertlist=$_ENV['expert']->getlist_by_cid($cid);
    	
    	return $expertlist;
    }

    /* 浏览问题 */

    function onview() {
    	$panneltype="hidefixed";
    	$useragent = $_SERVER['HTTP_USER_AGENT']; 
 
      //  $this->setting['stopcopy_on'] && $_ENV['question']->stopcopy(); //是否开启了防采集功能
        $qid = intval($this->get[2]); //接收qid参数
        if($this->user['uid']>0){
        		 $panneltype="hidefixed";
        }
        
     //  echo 'get2:'.$this->get[2]."<br>";
        // echo 'get3:'.$this->get[3]."<br>";
        //  echo 'get4:'.$this->get[4]."<br>";
         //  exit();
        $_ENV['question']->add_views($qid); //更新问题浏览次数
        $question = $_ENV['question']->get($qid);
       $topiclist = $_ENV['topic']->get_bycatid($question['cid'], 0, 8);
        empty($question) && $this->message('问题已经被删除！');
        (0 == $question['status']) && $this->message('问题正在审核中,请耐心等待！');
        /* 问题过期处理 */
        if ($question['endtime'] < $this->time && ($question['status'] == 1 || $question['status'] == 4)) {
            $question['status'] = 9;
            $_ENV['question']->update_status($qid, 9);
            $this->send($question['authorid'], $question['id'], 2);
        }
        $asktime = tdate($question['time']);
        $endtime = timeLength($question['endtime'] - $this->time);
        $solvetime = tdate($question['endtime']);
        $supplylist = $_ENV['question']->get_supply($question['id']);
        
     
         $ordertype = 1;
        if(strpos($this->get[3], 'u') == false ){
         if (isset($this->get[3]) && $this->get[3] == 1) {
            $ordertype = 2;
            $ordertitle = '倒序查看回答';
        } else {
        	
            $ordertype = 1;
            $ordertitle = '正序查看回答';
        }
        }else{
        	
        }
        $seo_userinfo="";
         $seo_answerinfo="";
         if(strpos($this->get[3], 'u') !== false ){
         	 $uids=explode('u-',$this->get[3]);
        	 $aids=explode('a-',$this->get[4]);
        	$user=$_ENV['user']->get_by_uid($uids[1]);
        	$seo_userinfo=$user['username']."的回答";
        	  $this->load('answer');
        	 $seo_answerinfo = $_ENV['answer']->get($aids[1]);
      $seo_answerinfo['content']=strip_tags( $seo_answerinfo['content']);
        	
         }
        //回答分页      
        @$page=0;  
        if(strpos($this->get[4], 'a') !== false ){
        	@$page = 1;
        	 
        }else{
        	@$page = max(1, intval($this->get[3]));
        }
    
        $pagesize =isset($this->setting['list_answernum']) ?$this->setting['list_answernum']:3;
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("answer", " qid=$qid AND status>0 AND adopttime =0");
        $answerlistarray = $_ENV['answer']->list_by_qid($qid, $ordertype, $rownum, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "question/view/$qid" );
        $answerlist = $answerlistarray[0];
        $already = $answerlistarray[1];
        $solvelist = $_ENV['question']->list_by_cfield_cvalue_status('cid', $question['cid'], 2);
        $nosolvelist = $_ENV['question']->list_by_cfield_cvalue_status('cid', $question['cid'], 1);
         
     
    	   
   
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        $expertlist = $_ENV['expert']->get_by_cid($question['cid']);
        $typearray = array('1' => 'nosolve', '2' => 'solve', '4' => 'nosolve', '6' => 'solve', '9' => 'close');
        $typedescarray = array('1' => '待解决', '2' => '已解决', '4' => '高悬赏', '6' => '已推荐', '9' => '已关闭');
        $navtitle = $question['title'];
        $dirction = $typearray[$question['status']];
        $bestanswer = $_ENV['answer']->get_best($qid);
        
       
        $categoryjs = $_ENV['category']->get_js();
        $taglist = $_ENV['tag']->get_by_qid($qid);
        $expertlist = $_ENV['expert']->get_by_cid($question['cid']);
         $is_followedauthor = $_ENV['user']->is_followed($question['authorid'], $this->user['uid']);
        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);
        $followerlist = $_ENV['question']->get_follower($qid);
        /* SEO */
        $curnavname = $navlist[count($navlist) - 1]['name'];
        if (!$bestanswer) {
            $bestanswer = array();
            $bestanswer['content'] = '';
        }else{
        	$user=$_ENV['user']->get_by_uid( $bestanswer['authorid']);
        	$bestanswer['signature']=$user['signature'];
        }
        
       //收藏的人
       $this->load("favorite");
       $favoritelist=$_ENV['favorite']->get_list_byqid($qid);
       
      
        if ($this->setting['seo_question_title']) {
            $seo_title = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_title']);
            $seo_title = str_replace("{wtbt}", $question['title'], $seo_title);
            $seo_title = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_title);
            $seo_title = str_replace("{flmc}", $curnavname, $seo_title);
           if($page!=1){
           	 $seo_title=$seo_title."-第".$page."页回答".$seo_userinfo;
           }else{
           	 $seo_title=$seo_title.$seo_userinfo;
           }
           
           
        }else{
         if($page!=1){
           	 $navtitle = $navtitle."-第".$page."页回答".$seo_userinfo;
           }else{
           	  $navtitle = $navtitle.$seo_userinfo;
           }
        	
        }
        if ($this->setting['seo_question_description']) {
        	$seo_description= $seo_answerinfo['content'];
            $seo_description = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_description']);
            $seo_description = str_replace("{wtbt}", $question['title'], $seo_description);
            $seo_description = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_description);
            $seo_description = str_replace("{flmc}", $curnavname, $seo_description);
            $seo_description = str_replace("{wtms}", strip_tags($question['description']), $seo_description);
            $seo_description = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_description);
            
        if(!$seo_answerinfo['content']){
        		$seo_description=$seo_description."。最佳回答：". strip_tags($bestanswer['content']);
        	}else{
        		$seo_description=$seo_description."。最佳回答：". $seo_answerinfo['content'];
        	}
        }else{
        	
        	if(!$seo_answerinfo['content']){
        		$seo_description= strip_tags($bestanswer['content']);
        	}else{
        		$seo_description= $seo_answerinfo['content'];
        	}
        	
        }
        if ($this->setting['seo_question_keywords']) {
        	$seo_keywords= $seo_answerinfo['content'];
            $seo_keywords = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_keywords']);
            $seo_keywords = str_replace("{wtbt}", $question['title'], $seo_keywords);
            $seo_keywords = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_keywords);
            $seo_keywords = str_replace("{flmc}", $curnavname, $seo_keywords);
            $seo_keywords = str_replace("{wtbq}", implode(",", $taglist), $seo_keywords);
            $seo_keywords = str_replace("{description}", strip_tags($question['description']), $seo_keywords);
            $seo_keywords = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_keywords);
        }else{
        	
       
        	  $seo_keywords = implode(",", $taglist);
        }
      //if($dirction=='close'){
      //	$dirction='nosolve';
   //   }
      $seo_description=str_replace('&nbsp;', '，', $seo_description);
      $bid=$bestanswer['id'];
   
       // include template($dirction);
  
       include template('solve');
    }

   
    function onanswer() {
        //只允许专家回答问题
//        if (isset($this->setting['allow_expert']) && $this->setting['allow_expert'] && !$this->user['expert']) {
//            $this->message('站点已设置为只允许专家回答问题，如有疑问请联系站长.');
//        }
//        $qid = $this->post['qid'];
//        $question = $_ENV['question']->get($qid);
//        if (!$question) {
//            $this->message('提交回答失败,问题不存在!');
//        }
//       	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
//        				if($this->user['active']!=1&&$this->user['groupid']!=1){
//        				
//        					   $this->message("必须激活邮箱才能回复!",'question/view/' . $qid );
//        				}
//        			}
//    	if(isset($this->setting['code_ask'])&&$this->setting['code_ask']=='1'){
//        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
//            $this->message($this->post['state']."验证码错误!", 'BACK');
//        }
//        				}
//        if ($this->user['uid'] == $question['authorid']) {
//            $this->message('提交回答失败，不能自问自答！', 'question/view/' . $qid);
//        }
//        $this->setting['code_ask'] && $this->checkcode(); //检查验证码
//        $already = $_ENV['question']->already($qid, $this->user['uid']);
//        $already && $this->message('不能重复回答同一个问题，可以修改自己的回答！', 'question/view/' . $qid);
//        $title = $this->post['title'];
//        $content = $this->post['content'];
//        //检查审核和内容外部URL过滤
//        $status = intval(2 != (2 & $this->setting['verify_question']));
//        $allow = $this->setting['allow_outer'];
//        if (3 != $allow && has_outer($content)) {
//            0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
//            1 == $allow && $status = 0;
//            2 == $allow && $content = filter_outer($content);
//        }
//        //检查违禁词
//        $contentarray = checkwords($content);
//        1 == $contentarray[0] && $status = 0;
//        2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
//        $content = $contentarray[1];
//
//        /* 检查提问数是否超过组设置 */
//        ($this->user['answerlimits'] && ($_ENV['userlog']->rownum_by_time('answer') >= $this->user['answerlimits'])) &&
//                $this->message("你已超过每小时最大回答数" . $this->user['answerlimits'] . ',请稍后再试！', 'BACK');
//
//
//$content_temp=str_replace('<p>', '', $content);
//$content_temp=str_replace('</p>', '', $content_temp);
//$content_temp=str_replace('&nbsp;', '', $content_temp);
//$content_temp= preg_replace("/\s+/",'',$content_temp);
//$content_temp = preg_replace('/s(?=s)/', '', $content_temp);
//$content_temp=trim($content_temp);
//         if(trim($content_temp)==''){
//         	$this->message('回答不能为空！', 'BACK');
//         }
//         if($this->user['groupid']==1){
//         	$status=2;
//         }
//         	
//         	
//         
//        $_ENV['answer']->add($qid, $title, $content, $status);
//        //回答问题，添加积分
//        $this->credit($this->user['uid'], $this->setting['credit1_answer'], $this->setting['credit2_answer']);
//        //给提问者发送通知
//        $this->send($question['authorid'], $question['id'], 0);
//        //如果ucenter开启，则postfeed
//        if ($this->setting["ucenter_open"] && $this->setting["ucenter_answer"]) {
//            $this->load('ucenter');
//            $_ENV['ucenter']->answer_feed($question, $content);
//        }
//        $viewurl = urlmap('question/view/' . $qid, 2);
//        $_ENV['userlog']->add('answer');
//        $_ENV['doing']->add($this->user['uid'], $this->user['username'], 2, $qid, $content);
//        if (0 == $status) {
//            $this->message('提交回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......', 'BACK');
//        } else {
//        	$quser= $_ENV['user']->get_by_uid($question['authorid']);
//        	 global $setting;
//        	$mpurl = SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
//        	 //发送邮件通知
//            $subject = "问题有新回答！" ;
//            $message = $content.'<p>现在您可以点击<a swaped="true" target="_blank" href="' . $mpurl . '">查看最新回复</a>。</p>';
//                 if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$quser['active']==1){
//                    sendmail($quser, $subject, $message);
//                 }
//            $this->message('提交回答成功！', $viewurl);
//        }
    }

    /* 采纳答案 */

    function onadopt() {
//        $qid = intval($this->post['qid']);
//        $aid = intval($this->post['aid']);
//        $comment = $this->post['content'];
//        $question = $_ENV['question']->get($qid);
//        $answer = $_ENV['answer']->get($aid);
//        $ret = $_ENV['answer']->adopt($qid, $answer);
//        if ($ret) {
//            $this->load("answer_comment");
//            $_ENV['answer_comment']->add($aid, $comment, $question['authorid'], $question['author']);
//            $this->credit($answer['authorid'], $this->setting['credit1_adopt'], intval($question['price'] + $this->setting['credit2_adopt']), 0, 'adopt');
//            $this->send($answer['authorid'], $question['id'], 1);
//            $viewurl = urlmap('question/view/' . $qid, 2);
//            $_ENV['doing']->add($question['authorid'], $question['author'], 8, $qid, $comment, $answer['id'], $answer['authorid'], $answer['content']);
//        }
//$quser= $_ENV['user']->get_by_uid($answer['authorid']);
//        	 global $setting;
//        	$mpurl = SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
//        	 //发送邮件通知
//            $subject = "你的问题被采纳(".$question['title'].")！" ;
//            $message = $comment.'<p>现在您可以点击<a swaped="true" target="_blank" href="' . $mpurl . '">查看详情</a>。</p>';
//             try{
//            if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$$quser['active']==1){
//            sendmail($quser, $subject, $message);
//                 }
//             }catch (Exception $e){
//             	 $this->message('采纳答案成功！', $viewurl);
//             }
//            $this->message('采纳答案成功！', $viewurl);
    }

    function onajaxadopt() {
    	 $message=array();
        $qid = intval($this->post['qid']);
        $aid = intval($this->post['aid']);
        $comment = $this->post['content'];
        $question = $_ENV['question']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        //判断问题是否被采纳过了
        if($question['status']==2){
        	 $message['message']='此问题已经采纳过了';
           echo json_encode($message);
             exit();
        }
        //判断这个回答是否被采纳过了
        if($answer['adopttime']>0){
        	 $message['message']='此回答已经采纳过了';
           echo json_encode($message);
             exit();
        }
        $ret = $_ENV['answer']->adopt($qid, $answer);
        
            $touid=$answer['authorid'];
                 $quid= $question['authorid'];
             
               
               
           
                 	
                 
               
        if ($ret) {
            $this->load("answer_comment");
            $_ENV['answer_comment']->add($aid, $comment, $question['authorid'], $question['author']);
            
            $this->credit($answer['authorid'], $this->setting['credit1_adopt'], intval($question['price'] + $this->setting['credit2_adopt']), 0, 'adopt');
           
            $this->send($answer['authorid'], $question['id'], 1);
            $viewurl = urlmap('question/view/' . $qid, 2);
            $_ENV['doing']->add($question['authorid'], $question['author'], 8, $qid, $comment, $answer['id'], $answer['authorid'], $answer['content']);
        }
$quser= $_ENV['user']->get_by_uid($answer['authorid']);
        	 global $setting;
        	$mpurl = SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
        	 //发送邮件通知
            $subject = "你的问题被采纳(".$question['title'].")！" ;
            $emailmessage = $comment.'<p>现在您可以点击<a swaped="true" target="_blank" href="' . $mpurl . '">查看详情</a>。</p>';
             try{
            if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$quser['active']==1){
            sendmail($quser, $subject, $emailmessage);
                 }
             }catch (Exception $e){
             	 $message['message']='ok';
           echo json_encode($message);
             exit();
             	 
             }
           	 $message['message']='ok';
           echo json_encode($message);
             exit();
    }
    /* 结束问题，没有满意的回答，还可直接结束提问，关闭问题。 */

    function onclose() {
        $qid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['qid'];
        $_ENV['question']->update_status($qid, 9);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    /* 补充提问细节 */

    function onsupply() {
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
      
        $question = $_ENV['question']->get($qid);
        if (!$question) {
            $this->message("问题不存在或已被删除!", "STOP");
        }
        if ($question['authorid'] != $this->user['uid'] || $this->user['uid'] == 0) {
            $this->message("非法操作!", "STOP");
            exit;
        }
    
       	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        				
        					   $this->message("必须激活邮箱才能补充!",'question/view/' . $qid );
        				}
        			}
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
        	if($this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
        				}
            $content = $this->post['content'];
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
            $content = $contentarray[1];

            $question = $_ENV['question']->get($qid);
            //问题最大补充数限制
            (count(unserialize($question['supply'])) >= $this->setting['apend_question_num']) && $this->message("您已超过问题最大补充次数" . $this->setting['apend_question_num'] . ",发布失败！", 'BACK');
 if($this->user['groupid']==1){
 	$status=1;
 }
            
            $_ENV['question']->add_supply($qid, $question['supply'], $content, $status); //添加问题补充
            $viewurl = urlmap('question/view/' . $qid, 2);
            if (0 == $status) {
                $this->message('补充问题成功！为了确保问答的质量，我们会对您的提问内容进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message('补充问题成功！', $viewurl);
            }
        }
        include template("supply");
    }
 function onajaxsupply() {
 	 $message=array();
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
      
        $question = $_ENV['question']->get($qid);
        if (!$question) {
          
            
             $message['message']="问题不存在或已被删除!";
           echo json_encode($message);
             exit();
             
        }
        if ($question['authorid'] != $this->user['uid'] || $this->user['uid'] == 0) {
         
               $message['message']="非法操作!";
           echo json_encode($message);
             exit();
        }
    
       	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        				
        					  
        					   
        					     $message['message']="必须激活邮箱才能补充!";
           echo json_encode($message);
             exit();
        				}
        			}

       
        	if($this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()&&$this->user['credit1']<$this->setting['jingyan']) {
           
            
            			     $message['message']="验证码错误!";
           echo json_encode($message);
             exit();
        }
        				}
            $content = $this->post['content'];
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
               if( 0 == $allow ){
              
               	 $message['message']="内容包含外部链接，发布失败!";
           echo json_encode($message);
             exit();
               }
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            if(2 == $contentarray[0] ){
        
            	 $message['message']="内容包含非法关键词，发布失败!";
           echo json_encode($message);
             exit();
            }
            $content = $contentarray[1];

            $question = $_ENV['question']->get($qid);
            //问题最大补充数限制
           if (count(unserialize($question['supply'])) >= $this->setting['apend_question_num']) {

 $message['message']="您已超过问题最大补充次数" . $this->setting['apend_question_num'] . ",发布失败！";

 
 
 
 echo json_encode($message);
             exit();
           
           }
 if($this->user['groupid']==1){
 	$status=1;
 }
            
            $_ENV['question']->add_supply($qid, $question['supply'], $content, $status); //添加问题补充
            $viewurl = urlmap('question/view/' . $qid, 2);
            if (0 == $status) {
            

  $message['url']= SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'];
             $message['sh']=1;
            $message['message']='ok';
 
 
 echo json_encode($message);
             exit();
            
            } else {
                
               
  $message['url']= SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'];
          
            $message['message']='ok';
           echo json_encode($message);
             exit();
            }
        
       
    }
    /* 追加悬赏 */

    function onaddscore() {
        $qid = intval($this->post['qid']);
        $score = abs($this->post['score']);
        if ($this->user['credit2'] < $score) {
            $this->message("财富值不足!", 'BACK');
        }
        $_ENV['question']->update_score($qid, $score);
        $this->credit($this->user['uid'], 0, -$score, 0, 'offer');
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('追加悬赏成功！', $viewurl);
    }

    /* 修改回答 */

    function oneditanswer() {
        $navtitle = '修改回答';
        $aid = $this->get[2] ? $this->get[2] : $this->post['aid'];
        $answer = $_ENV['answer']->get($aid);
        
        //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$answer['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        	$this->message("非法操作,您的ip已被系统记录！", "STOP");
        }
    
        
          	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        				
        					   $this->message("必须激活邮箱才能修改回答!",'question/view/' . $answer['qid'] );
        				}
        			}
        
        (!$answer) && $this->message("回答不存在或已被删除！", "STOP");
        $question = $_ENV['question']->get($answer['qid']);
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
       
        include template("editanswer");
    }
 function onajaxeditanswer() {
         $message=array();
        $aid = $this->get[2] ? $this->get[2] : $this->post['aid'];
        $answer = $_ENV['answer']->get($aid);
        
        //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$answer['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        
        	  $message['message']="非法操作,您的ip已被系统记录！";
           echo json_encode($message);
             exit();
        }
    
        
          	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        				
        					
        				 $message['message']="必须激活邮箱才能修改回答!";
           echo json_encode($message);
             exit();
        				
        				}
        			}
        
        if(!$answer){
        	
        	
        	 $message['message']="回答不存在或已被删除！";
           echo json_encode($message);
             exit();
        }
        $question = $_ENV['question']->get($answer['qid']);
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
        	if($this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()&&$this->user['credit1']<$this->setting['jingyan']) {
        
            
             	
        	 $message['message']="验证码错误!";
           echo json_encode($message);
             exit();
             
        }
        				}
            $content = $this->post['content'];
            $viewurl = urlmap('question/view/' . $question['id'], 2);

            //检查审核和内容外部URL过滤
            $status = intval(2 != (2 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
               if( 0 == $allow ){
              
               	
               	 $message['message']="内容包含外部链接，发布失败!";
           echo json_encode($message);
             exit();
               }
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
           if( 2 == $contentarray[0]){
         
           	
           		 $message['message']="内容包含非法关键词，发布失败!";
           echo json_encode($message);
             exit();
           }
            $content = $contentarray[1];

         if($this->user['groupid']==1){
         	$status=2;
         }
            $_ENV['answer']->update_content($aid, $content, $status);
$quser= $_ENV['user']->get_by_uid($question['authorid']);
        	 global $setting;
        	$mpurl = SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
        	 //发送邮件通知
            $subject = "问题有新回答！" ;
            $emailmessage = $content.'<p>现在您可以点击<a swaped="true" target="_blank" href="' . $mpurl . '">查看最新回复</a>。</p>';
                 if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$quser['active']==1){
            sendmail($quser, $subject, $emailmessage);
                 }
            if (0 == $status) {
               $message['sh']=1;
            }
            
             $message['url']= $mpurl;
             
            $message['message']='ok';
 
 
 echo json_encode($message);
             exit();
        }
        
    }
    //搜索全部问题

    //搜索问题
    function searchquestion($word,$qstatus){
    
        
          $questionlist = $_ENV['question']->search_title($word, $qstatus, 0, 0,$this->serach_num);
        
          $lis='';
        
         
          foreach ($questionlist as $key=>$val){
          	$title=$questionlist[$key]['title'];
          $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
             $title=str_replace('<em>', '', strtolower( $title));
                $title=str_replace('</em>', '', strtolower($title));
                     $title=str_replace('&lt;font color=red&gt;', '', strtolower( $title));
                          $title=str_replace('&lt;/font&gt;', '', strtolower( $title));
          	  $li=' <li class="item qitem" data-index="'.$key.'"><a href="'.SITE_URL.$suffix.'q-' . $questionlist[$key]['id'].$fix.'" text="网页提问词语联想第'.$key.'条">'.strip_tags($title).'</a> </li>';
	        	  $lis=$lis.$li;
          }
           echo $lis;
           exit();
    }
    //搜索文章
    function searcharticle($word){
         $topiclist = $_ENV['topic']->list_by_tag($word, 0,$this->serach_num);
           if($topiclist==null){
           	
           	
              
            $topiclist = $_ENV['topic']->get_bylikename($word, 0, $this->serach_num);
           }
           
               $lis='';
        
              $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
       
          foreach ($topiclist as $key=>$val){
          	$title=$topiclist[$key]['title'];
          $imgurl=$topiclist[$key]['image'];
        
           
      
          $index=strpos($imgurl,'http');
           if ($index!=0){
           	$imgurl=SITE_URL.$imgurl;
           }
             $title=str_replace('<em>', '', strtolower( $title));
                $title=str_replace('</em>', '', strtolower($title));
                     $title=str_replace('&lt;font color=red&gt;', '', strtolower( $title));
                          $title=str_replace('&lt;/font&gt;', '', strtolower( $title));
          	  $li=' <li class="item articleitem" data-index="'.$key.'">
          	  <a href="'.SITE_URL.$suffix.'article-' . $topiclist[$key]['id'].$fix.'" text="网页提问词语联想第'.$key.'条">'
          	  .'<div class="row"><div class="col-sm-3">
          	  <img class="img-rounded pull-left" width="80" height="50" src="'.$imgurl.'" />
          	  </div><div class="col-sm-9 "><p class="art-desc pull-left color-white">'.str_replace('&nbsp;','',strip_tags($title)).'</p>
          	 
          	  
          	  </div></div>'.
          	  '</a> </li>';
	        	  $lis=$lis.$li;
          }
           echo $lis;
           exit();
    }
    //搜索标签
    function searchtag($word){
    	
    	  $taglist = $_ENV['tag']->list_by_tagname($word, 0, $this->serach_num);
    	     $lis='';
        
              $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
     if($taglist){
        	$lis='<li class="list-group-item bold nopadding">问题话题<hr><li>';
        }
          foreach ($taglist as $key=>$val){
          	$title=$taglist[$key]['name'];
        $qcountarr=$taglist[$key]['count'];
        $qcount=$qcountarr['sum'];
             $title=str_replace('<em>', '', strtolower( $title));
                $title=str_replace('</em>', '', strtolower($title));
                     $title=str_replace('&lt;font color=red&gt;', '', strtolower( $title));
                          $title=str_replace('&lt;/font&gt;', '', strtolower( $title));
          	  $li=' <li class="item tagitem" data-index="'.$key.'"><a href="'.SITE_URL.$suffix.'tag-' . $taglist[$key]['name'].$fix.'" ><span class="label label-danger pull-left mar-l-05 mar-t-05">'.strip_tags($title).'</span><span class="pull-right  mar-r-1 font-12">'.$qcount.'个讨论</span></a> </li>';
	        	  $lis=$lis.$li;
          }
          
          
            $topictaglist = $_ENV['topic_tag']->list_by_tagname($word, 0, $this->serach_num);
       if($topictaglist){
        	$lis='<li class="list-group-item bold nopadding">文章话题<hr><li>';
        }
       foreach ($topictaglist as $key=>$val){
          	$title=$topictaglist[$key]['name'];
        $qcountarr=$topictaglist[$key]['count'];
        $qcount=$qcountarr['sum'];
             $title=str_replace('<em>', '', strtolower( $title));
                $title=str_replace('</em>', '', strtolower($title));
                     $title=str_replace('&lt;font color=red&gt;', '', strtolower( $title));
                          $title=str_replace('&lt;/font&gt;', '', strtolower( $title));
          	  $li=' <li class="item tagitem" data-index="'.$key.'"><a href="'.SITE_URL.$suffix.'tag-' . $topictaglist[$key]['name'].$fix.'" ><span class="label label-danger pull-left mar-l-05 mar-t-05">'.strip_tags($title).'</span><span class="pull-right  mar-r-1 font-12">'.$qcount.'个讨论</span></a> </li>';
	        	  $lis=$lis.$li;
          }
          
           echo $lis;
           exit();
    	  
    }
    //搜索用户
    function searchuser($word){
    	
    	
    	 $userlist = $_ENV['user']->list_by_search_condition(" username like '%$word%'", 0, $this->serach_num);
    
     $lis='';
        
              $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
       
          foreach ($userlist as $key=>$val){
          	$username=$userlist[$key]['username'];
          		$avatar=$userlist[$key]['avatar'];
          	$uid=$userlist[$key]['uid'];
          	$answers=$userlist[$key]['answers'];
          	$followers=$userlist[$key]['followers'];
        
          	  $li=' <li class="useritem" data-index="'.$key.'">
          	  <div class="row clear"><div class="col-sm-2"><img width="45" height="45" class="img-rounded" src="'.$avatar.'" alt="'.$username.'" /></div>
          	  <div class="col-sm-10">
          	  <a class="text-danger clear bold font-12" href="'.SITE_URL.$suffix.'u-'.$uid.$fix.'">'.$username.'</a>
          	 
          	  <span class="text-danger mar-ly-05">回答( '.$answers.')</span><span class="text-danger mar-ly-05">关注('.$followers.')</span>
          	 
          	  </div>
          	  </div>
          	   </li>';
	        	  $lis=$lis.$li;
          }
           echo $lis;
           exit();
    
    
    }
    /* 搜索页面 */
        function onsearchkey() {
        	
        	if($this->post['word']){
        	if(is_mobile()){
        		   header("Location:".SITE_URL.'?q='.urlencode($this->post['word']));
        
     exit();
        	}
        		$tagid=$this->post['tagid'];
        			$qstatus = $status = $this->get[3] ? $this->get[3] : 1;
        (1 == $status) && ($qstatus = "1,2,6,9");
        (2 == $status) && ($qstatus = "2,6");
        	  $word = trim($this->post['word']) ? trim($this->post['word']) : urldecode($this->get[2]);
        $word = str_replace(array("\\","'"," ","/","&"),"", $word);
        $word = strip_tags($word);
        $word = htmlspecialchars($word);
        $word = taddslashes($word, 1);
        		switch ($tagid){
        			case '0':
        				$this->searchquestion($word,$qstatus);
        				break;
        				case '1':
        					$this->searchquestion($word,$qstatus);
        				break;
        				case '2':
        					$this->searcharticle($word);
        				break;
        				case '3':
        					$this->searchtag($word);
        				break;
        				case '4':
        					$this->searchuser($word);
        				break;
        		}
        		 
        	}else{
        		 include template("searchkey");
        	}
        	
         
       
        }
    /* 搜索问题 */

    function onsearch() {
     $hidefooter='hidefooter';
    	$type="question";
        $qstatus = $status = $this->get[3] ? $this->get[3] : 1;
        (1 == $status) && ($qstatus = "1,2,6,9");
        (2 == $status) && ($qstatus = "2,6");
        if($this->post['word']){
        header("Location:".SITE_URL.'?q='.urlencode($this->post['word']));
        
     exit();
        }
        
   
         $_word=isset($this->get[2]) ? urldecode($this->get[2]):'ask2';
        if(isset($_SERVER['HTTP_X_REWRITE_URL'])){
        		
         if(function_exists("iconv")&&$this->get[2]!=null){
       	$_word= iconv("GB2312", "UTF-8//IGNORE", $this->get[2]);
       	
       }
        }
        $word = trim($this->post['word']) ? trim($this->post['word']) :urldecode($_word);
        $word = str_replace(array("\\","'"," ","/","&"),"", $word);
        $word = strip_tags($word);
        $word = htmlspecialchars($word);
        $word = taddslashes($word, 1);
       
        (!$word) && $this->message("搜索关键词不能为空!", 'BACK');
        if(strpos($this->get[1], 'tag')>0){
        	 $navtitle = $word ;
        	 
        }else{
        	$navtitle = $word ;
        }
        $seo_keywords= $word;
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        if (preg_match("/^tag:(.+)/", $word, $tagarr)) {
            $tag = $tagarr[1];
           $rownum = $_ENV['question']->rownum_by_tag($tag, $qstatus);
            $questionlist1 = $_ENV['question']->list_by_tag($tag, $qstatus, $startindex, $pagesize);
        } else {
            $questionlist1 = $_ENV['question']->search_title($word, $qstatus, 0, $startindex, $pagesize);
            $rownum = $_ENV['question']->search_title_num($word, $qstatus);
        }
			//if(count($questionlist)==0){
				$tagarr=dz_segment($word);
			//	print_r($tagarr);
				//exit();
				if(count($tagarr)>0){
					 $tag = $tagarr[0];
					  $rownum = $_ENV['question']->rownum_by_tag($tag, $qstatus);
					 $questionlist2=$_ENV['question']->list_by_tag($tag,$qstatus, $startindex, $pagesize);
				}
				
				
			//}
			$questionlist=array_merge($questionlist1,$questionlist2);
			$rownum=count($questionlist);
			if($rownum==0){
				  $seo_keywords="";
				  $navtitle='暂无搜索相关信息';
				 
			}else{
				
				  $navtitle="关于-$word-的相关搜索";
			}
        $related_words = $_ENV['question']->get_related_words();
        $hot_words = $_ENV['question']->get_hot_words();
        $corrected_words = $_ENV['question']->get_corrected_word($word);
        $departstr = page($rownum, $pagesize, $page, "question/search/$word/$status");
        include template('search');
    }

    /* 提问自动搜索已经解决的问题 */

    function onajaxsearch() {
        $title = $this->get[2];
        $questionlist = $_ENV['question']->search_title($title, 2, 1, 0, 5);
        include template('ajaxsearch');
    }

    /* 顶指定问题 */

    function onajaxgood() {
        $qid = $this->get[2];
        $tgood = tcookie('good_' . $qid);
        !empty($tgood) && exit('-1');
        $_ENV['question']->update_goods($qid);
        tcookie('good_' . $qid, $qid);
        exit('1');
    }

    function ondelete() {
    	   $question = $_ENV['question']->get($this->get[2]);
    	
    	 //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$question['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        	$this->message("非法操作,您的ip已被系统记录！", "STOP");
        }
   
               $touser = $_ENV['user']->get_by_uid($question['authorid']);
                if(isset($this->setting['notify_mail'])&&$this->setting['notify_mail']=='1'&&$touser['active']==1){
                	sendmail($touser, '您的问题'.$question['title'].'已被删除');
                }
                $this->credit($question['authorid'], 0, $question['price'], 0, 'back');
        $_ENV['question']->remove(intval($this->get[2]));
        
      
           $this->message('问题删除成功！',urlmap('index/default'));
    }

    //问题推荐
    function onrecommend() {
        $qid = intval($this->get[2]);
        $_ENV['question']->change_recommend($qid, 6, 2);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('问题推荐成功!', $viewurl);
    }

    //编辑问题
    function onedit() {
        $navtitle = '编辑问题';
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
           $question = $_ENV['question']->get($qid);
     	 //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$question['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        	$this->message("非法操作,您的ip已被系统记录！", "STOP");
        }
        
     
        if (!$question)
            $this->message("问题不存在或已被删除!", "STOP");
    	if($this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
        				}
    	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        			
        					   $this->message("必须激活邮箱才能编辑问题!", urlmap('question/view/' . $qid, 2) );
        				}
        			}
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $viewurl = urlmap('question/view/' . $qid, 2);
            $title = trim($this->post['title']);
            (!trim($title)) && $this->message('问题标题不能为空!', $viewurl);
            $_ENV['question']->update_content($qid, $title, $this->post['content']);
            $this->message('问题编辑成功!', $viewurl);
        }
        include template("editquestion");
    }
    function onajaxedit() {
       $message=array();
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question)
        {
        	    $message['message']="问题不存在或已被删除!";
           echo json_encode($message);
             exit();
        }
    	 //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$question['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        	 $message['message']="error!";
           echo json_encode($message);
             exit();
        }
         
    	if($this->user['grouptype']!=1){
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
         
                $message['message']="验证码错误!";
           echo json_encode($message);
             exit();
        }
        				}
    	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        			
        					 
        				  $message['message']="必须激活邮箱才能编辑问题!";
           echo json_encode($message);
             exit();
        				
        				}
        			}
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $viewurl = urlmap('question/view/' . $qid, 2);
            $title = trim($this->post['title']);
           if (!trim($title)){
           	
           				 
        				  $message['message']='问题标题不能为空!';
           echo json_encode($message);
             exit();
        				
           }
            $_ENV['question']->update_content($qid, $title, $this->post['content']);
            global $setting;
            $message['url']= SITE_URL . $setting['seo_prefix'] . $viewurl.$setting['seo_suffix'];
              $message['message']='ok';
           echo json_encode($message);
             exit();
             
        }
       
    }

    //编辑标签
    function onedittag() {
        $tag = trim($this->post['qtags']);
        $qid = intval($this->post['qid']);
        $viewurl = urlmap("question/view/$qid", 2);
        $message = $tag ? "标签修改成功!" : "标签不能为空!";
        $taglist = explode(" ", $tag);
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
        $this->message($message, $viewurl);
    }

    //移动分类
    function onmovecategory() {
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
            $qid = $this->post['qid'];
            $viewurl = urlmap('question/view/' . $qid, 2);
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
                $this->message('分类不存在，请更下缓存!', $viewurl);
            }
            $_ENV['question']->update_category($qid, $cid, $cid1, $cid2, $cid3);
            $this->message('问题分类修改成功!', $viewurl);
        }
    }

    //设为未解决
    function onnosolve() {
        $qid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['question']->change_to_nosolve($qid);
        $this->message('问题状态设置成功!', $viewurl);
    }

    //前台删除问题回答
    function ondeleteanswer() {
    	if($this->user['uid']==0){
    		  $this->message("你还没登录!", 'user/login');
    	}
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $answer=$_ENV['answer']->get($aid);
        if($answer['authorid']!=$this->user['uid']&&$this->user['grouptype']!=1){
        	$this->message("非法操作!", $viewurl);
        }
        $_ENV['answer']->remove_by_qid($aid, $qid);
        $this->message("回答删除成功!", $viewurl);
    }

    //前台审核回答
    function onverifyanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['answer']->change_to_verify($aid);
        $this->message("回答审核完成!", $viewurl);
    }

    //问题关注
    function onattentto() {
        $qid = intval($this->get[2]);
        if (!$qid) {
        $this->message("问题不存在!");
        }
        if($this->user['uid']==0){
        	 $this->message("游客不能收藏!");
        }
        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);
        if ($is_followed) {
            $_ENV['user']->unfollow($qid, $this->user['uid']);
             $_ENV['doing']->deletedoing($this->user['uid'],4,$qid);
             $this->message("已取消收藏!");
        } else {
            $_ENV['user']->follow($qid, $this->user['uid'], $this->user['username']);
            $question = taddslashes($_ENV['question']->get($qid), 1);
            $msgfrom = $this->setting['site_name'] . '管理员';
            $username = addslashes($this->user['username']);
            $this->load("message");
            $viewurl = url('question/view/' . $qid, 1);
            $_ENV['message']->add($msgfrom, 0, $question['authorid'], $username . "刚刚关注了您的问题", '<a target="_blank" href="' . url('user/space/' . $this->user['uid'], 1) . '">' . $username . '</a> 刚刚关注了您的问题' . $question['title'] . '"<br /> <a href="' . $viewurl . '">点击查看</a>');
            $_ENV['doing']->add($this->user['uid'], $this->user['username'], 4, $qid);
            
             $this->message("问题收藏成功!");
        }
       
    }

    function onfollow() {
        $qid = intval($this->get[2]);
        $question = taddslashes($_ENV['question']->get($qid), 1);
        if (!$question) {
            $this->message("问题不存在!");
            exit;
        }
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $followerlist = $_ENV['question']->get_follower($qid, $startindex, $pagesize);
        $rownum = $this->db->fetch_total('question_attention', " qid=$qid ");
        $departstr = page($rownum, $pagesize, $page, "question/follow/$qid");
        include template("question_follower");
    }

}

?>