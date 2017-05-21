<?php

!defined('IN_ASK2') && exit('Access Denied');

class usercontrol extends base {
var $whitelist;

    function usercontrol(& $get, & $post) {
    	
        $this->base($get, $post);
        $this->load('user');
        $this->load('topic');
        $this->load('question');
        $this->load('answer');
         $this->load("category");
        $this->load("favorite");
         
         $this->whitelist="search,spacefollower,vertifyemail,editemail,sendcheckmail,space_attention,userzhangdan,userbank,postrequestmoney,getsmscode";
    }

    function ondefault() {
    	
        $this->onscore();
    }

    function oncode() {
        ob_clean();
        $code = random(4);
        $_ENV['user']->save_code(strtolower($code));
        makecode($code);
    }
    
    function onsearch(){
    	      $hidefooter='hidefooter';
    	        $type="user";
        $word =urldecode($this->get[2]);
        $word = str_replace(array("\\","'"," ","/","&"),"", $word);
        $word = strip_tags($word);
        $word = htmlspecialchars($word);
        $word = taddslashes($word, 1);
        (!$word) && $this->message("搜索关键词不能为空!", 'BACK');
        $navtitle = $word ;
        @$page = max(1, intval($this->get[3]));
       // var_dump($this->get);exit();
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
          $seo_description=$word;
     $seo_keywords= $word;
        $rownum = $this->db->fetch_total('user', " username like '%$word%'");
    	 $userlist = $_ENV['user']->list_by_search_condition(" username like '%$word%'",$startindex, $pagesize);
    
    	    $departstr = page($rownum, $pagesize, $page, "user/search/$word");
        include template('serach_huser');
    }
    function onxinzhi(){
    	
    	include template('myxinzhi');
    }
   function onaddxinzhi(){
 
   	if($this->user['doarticle']==0&&$this->user['grouptype']!=1){
   		   $this->message('您所在用户组站长设置不允许发布文章！','topic/default');
   	}
    if (isset($this->post['submit'])) {
   
//    	if(trim($this->post['code'])==''&&$this->user['grouptype']!=1&&$this->user['credit1']<$this->setting['jingyan']){
//    		  $this->message($this->post['state']."验证码不能为空!", 'BACK');
//    	}
//    			   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()&&$this->user['grouptype']!=1&&$this->user['credit1']<$this->setting['jingyan']) {
//            $this->message($this->post['state']."验证码错误!", 'BACK');
//        }
    	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'&&$this->user['grouptype']!=1){
        				if($this->user['active']!=1){
        					$viewhref=urlmap('user/editemail',1);
        					   $this->message("必须激活邮箱才能发布文章!",$viewhref );
        				}
        			}
    	if($this->user['isblack']==1){
        $this->message('黑名单用户无法发布文章！','index/default');
        	}
       /* 检查提问数是否超过组设置 */
        			 $this->load("userlog");
        if(($_ENV['userlog']->rownum_by_time('topic') >= $this->user['articlelimits'])&&$this->user['grouptype']!=1) 
        
        {
        	
        	 
        	
                $this->message('你已超过每小时最大文章发布数,请稍后再试！','user/addxinzhi');
                                   exit();
        }
        				
    	 $this->load("topic");
    	
    	    	 $this->load("topic_tag");
    	    	
            $title = $this->post['title'];
             $topic_price =0;
            
          $topic_tag = $this->post['topic_tag'];
           $ataglist = explode(",", $topic_tag);
            $desrc = $this->post['content'];
              $outimgurl = $this->post['outimgurl'];
            // $tagarr= dz_segment($title,$desrc);
               $acid = $this->post['topicclass'];
              // if($ataglist!=null){
               //	$tagarr=array_merge($ataglist,$tagarr);
               //}
               
          
               if($acid==null)$acid=1;
         
            if ('' == $title || '' == $desrc) {
            	 $this->message('请完整填写专题相关参数!','user/addxinzhi');
               
                exit;
            }
              if($_FILES['image']['name']==null&&trim($outimgurl)==''){
              	 $this->message('封面图和外部图片至少填写一个!','user/addxinzhi');
               
                exit;
              }
            if($_FILES['image']['name']!=null&&trim($outimgurl)==''){
            	
           
               $imgname = strtolower($_FILES['image']['name']);
            $type = substr(strrchr($imgname, '.'), 1);
            if (!isimage($type)) {
            	 $this->message('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！','user/addxinzhi');
              
                exit;
            }
            $upload_tmp_file = ASK2_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;

            $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
            forcemkdir(ASK2_ROOT . '/data/attach/topic');
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                image_resize($upload_tmp_file, ASK2_ROOT . $filepath, 270, 220);
   
                //try{
//                require_once ASK2_STATIC_ROOT.'/js/neweditor/php/Config.php';
//                if(Config::OPEN_OSS){
//                
//                require_once ASK2_STATIC_ROOT.'/js/neweditor/php/up.php';
//            if(Common::getOpenoss()=='1'){
//$diross=$filepath;
//$tmpfile=$filepath;
//
//if(substr($filepath, 0,1)=='/'){
//	$diross=substr($filepath, 1);
//}
//$filepath=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT . $filepath);
//if($filepath!='error'){
//	//unlink(ASK2_ROOT . $tmpfile);
//}
//            }
//}
//                }catch (Exception $e){
//                	print $e->getMessage();  
//                }
         
            } else {
              
                $this->message('服务器忙，请稍后再试！','user/addxinzhi');
            }
             }
             if(trim($outimgurl)!=''){
             	$filepath=$outimgurl;
             }
                  $aid= $_ENV['topic']->addtopic($title, $desrc, $filepath,$this->user['username'],$this->user['uid'],1,$acid,$topic_price);
//$tag=implode(',',$tagarr);
               // $taglist = explode(",", $tag);
                  $_ENV['userlog']->add('topic');
     $ataglist && $_ENV['topic_tag']->multi_add(array_unique($ataglist),  $aid);
     
                      $this->load("doing");
               $_ENV['doing']->add($this->user['uid'], $this->user['username'], 9, $aid, $title);
                $this->message('添加成功！','article-'.$aid);
        } else {
        	// $this->load("topicclass");
        	//$topiclist=  $_ENV['topicclass']->get_list();
         if ($this->user['uid']==0||$this->user['uid']==null) {
           $this->message('您还没有登录！', 'user/login');
        }
          
               $categoryjs = $_ENV['category']->get_js();
           include template('addxinzhi');
        }
    	
    }
     function ondeletexinzhi(){
           if ($this->user['uid']==0||$this->user['uid']==null) {
           $this->message('非法操作，你的ip已被记录');
        }
     	 $this->load("topic");
     	 
     	  $topic = $_ENV['topic']->get(intval($this->get[2]));
     	  
     	  if($this->user['uid']!=$topic['authorid']&&$this->user['grouptype']!=1){
     	  	$this->message('非法操作，你的ip已被记录');
     	  }
     	  $_ENV['topic']->remove(intval($this->get[2]));
     	    $this->message('文章删除成功！','topic/default');
     }
 function oneditxinzhi(){
 		session_start();
 		$this->load("topic");
 	  $this->load("topic_tag");
 	    $topic = $_ENV['topic']->get(intval($this->get[2]));
    	 //判断当前用户是不是超级管理员
        $candone=false;
        if($this->user['grouptype']==1){
        	$candone=true;
        }else{
        	 //判断当前用户是不是回答者本人
        	 
        	if($this->user['uid']==$topic['authorid']){
        		$candone=true;
        	}
        }
     
        if($candone==false){
        	$this->message("非法操作,您的ip已被系统记录！", "STOP");
        }
    if (isset($this->post['submit'])) {
    	 
    	   $tid = intval($this->post['id']);
    	    $topic = $_ENV['topic']->get($tid);
    	
    	 if($topic['authorid']!=$this->user['uid']&&$candone==false){
    	 	$this->message('非法操作，你的ip已被记录');
    	 }
    	if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
        				if($this->user['active']!=1){
        					$viewhref=urlmap('user/editemail',1);
        					   $this->message("必须激活邮箱才能修改文章!",$viewhref );
        				}
        			}
                  $title = $this->post['title'];
                   $topic_price =0;
       $topic_tag = $this->post['topic_tag'];
            $taglist = explode(",", $topic_tag);
            $desrc = $this->post['content'];
               $outimgurl = $this->post['outimgurl'];
            $upimg=$this->post['upimg'];
          $views=$this->post['views'];
        $isphone= $this->post['isphone'];
            if($isphone=='on'){
            	$isphone=1;
            }else{
            	$isphone=0;
            }
             $acid = $this->post['topicclass'];
      // $tagarr= dz_segment($title,$desrc);
           
              // if($taglist!=null){
               //	$tagarr=array_merge($taglist,$tagarr);
              // }
               if($acid==null)$acid=1;
            $imgname = strtolower($_FILES['image']['name']);
            if ('' == $title || '' == $desrc) {
                $this->message('请完整填写专题相关参数!', 'errormsg');
                exit;
            }
             // print_r($tagarr);
            // exit();
            if ($imgname) {
                $type = substr(strrchr($imgname, '.'), 1);
                if (!isimage($type)) {
                    $this->message('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！', 'errormsg');
                    exit;
                }
                $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
                $upload_tmp_file = ASK2_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;
                forcemkdir(ASK2_ROOT . '/data/attach/topic');
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                    image_resize($upload_tmp_file, ASK2_ROOT . $filepath, 270, 220);
//                      try{
//                require_once ASK2_STATIC_ROOT.'/js/neweditor/php/Config.php';
//                if(Config::OPEN_OSS){
//                
//                 require_once ASK2_STATIC_ROOT.'/js/neweditor/php/up.php';
//            if(Common::getOpenoss()=='1'){
//$diross=$filepath;
//$tmpfile=$filepath;
//
//if(substr($filepath, 0,1)=='/'){
//	$diross=substr($filepath, 1);
//}
//$filepath=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT . $filepath);
//if($filepath!='error'){
//	//unlink(ASK2_ROOT . $tmpfile);
//}
//}
//                
//}
//                }catch (Exception $e){
//                	print $e->getMessage();  
//                }
                       $ispc=$topic['ispc'];
                    $_ENV['topic']->updatetopic($tid, $title, $desrc, $filepath,$isphone,$views,$acid,$ispc,$topic_price);
 $taglist && $_ENV['topic_tag']->multi_add(array_unique($taglist), $tid);
                    $this->message('文章修改成功！','article-'.$tid);
                } else {
                    $this->message('服务器忙，请稍后再试！');
                }
            } else {
            	if($outimgurl!=$upimg&&trim($upimg)!=''){
            		$upimg=$outimgurl;
            	}
            	$ispc=$topic['ispc'];
                $_ENV['topic']->updatetopic($tid, $title, $desrc,$upimg,$isphone,$views,$acid,$ispc,$topic_price);
            	  $taglist && $_ENV['topic_tag']->multi_add(array_unique($taglist), $tid);
                $this->message('文章修改成功！','article-'.$tid);
            }
        } else {
        	
         if ($this->user['uid']==0||$this->user['uid']==null) {
           $this->message('您还没有登录！', 'user/login');
        }
       
         
         $tagmodel=$_ENV['topic_tag']->get_by_aid($topic['id']);
         
        
         $topic['topic_tag']=implode(',', $tagmodel);
        
           $_SESSION["userid"]= getRandChar(56);
          $catmodel=$_ENV['category']->get($topic['articleclassid']);
               $categoryjs = $_ENV['category']->get_js();
           include template('editxinzhi');
        }
    	
    }
 function onregtip() {
     
	 include template('regtip');

     }
    function onregister() {
        if ($this->user['uid']) {
            header("Location:" . SITE_URL);
        }
       	$useragent = $_SERVER['HTTP_USER_AGENT']; 
        if (strstr($useragent, 'MicroMessenger')) { 
        	$wxbrower=true;
        }
        $navtitle = '注册新用户';
        if (!$this->setting['allow_register']) {
            $this->message("系统注册功能暂时处于关闭状态!", 'STOP');
        }
        if (isset($this->setting['max_register_num']) && $this->setting['max_register_num'] && !$_ENV['user']->is_allowed_register()) {
            $this->message("您的当前的IP已经超过当日最大注册数目，如有疑问请联系管理员!", 'STOP');
            exit;
        }
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        
     
        $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
//        if (isset($this->post['submit'])) {
//         
//           if(preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$this->post['username'])){ 
// $this->message("用户名包含特殊字符", 'user/register'); exit("shit!");
//}
//            $username = strip_tags(trim($this->post['username']));
//            $password = trim($this->post['password']);
//            $email = $this->post['email'];
//            if ('' == $username || '' == $password) {
//                $this->message("用户名或密码不能为空!", 'user/register');
//            } else if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $email)) {
//                $this->message("邮件地址不合法!", 'user/register');
//            } else if ($this->db->fetch_total('user', " email='$email' ")) {
//                $this->message("此邮件地址已经注册!", 'user/register');
//            } else if (!$_ENV['user']->check_usernamecensor($username)) {
//                $this->message("邮件地址被禁止注册!", 'user/register');
//            }
//            $this->setting['code_register'] && $this->checkcode(); //检查验证码
//            $user = $_ENV['user']->get_by_username($username);
//            $user && $this->message("用户名 $username 已经存在!", 'user/register');
//            //ucenter注册成功，则不会继续执行后面的代码。
//            if ($this->setting["ucenter_open"]) {
//                $this->load('ucenter');
//                $_ENV['ucenter']->register();
//            }
//            $uid = $_ENV['user']->add($username, $password, $email);
//            $_ENV['user']->refresh($uid);
//            $this->credit($this->user['uid'], $this->setting['credit1_register'], $this->setting['credit2_register']); //注册增加积分
//            //通行证处理
//            $forward = isset($this->post['forward']) ? $this->post['forward'] : SITE_URL;
//            $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward);
//            //发送邮件通知
//            $subject = "恭喜您在" . $this->setting['site_name'] . "注册成功！";
//            $message = '<p>现在您可以登录<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>自由的提问和回答问题。祝您使用愉快。</p>';
//            sendmail($this->user, $subject, $message);
//            $this->message('恭喜，注册成功！');
//        }
        
    
    
	 include template('register');

    }

    function onlogin() {
    	
    	
        if ($this->user['uid']) {
        	
            header("Location:" . SITE_URL);
        }
        
    	$useragent = $_SERVER['HTTP_USER_AGENT']; 
        if (strstr($useragent, 'MicroMessenger')) { 
        	$wxbrower=true;
        }
        $navtitle = '用户登录';
      //  $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
//        if (isset($this->post['submit'])) {
//        
//        	
//            $username = trim($this->post['username']);
//            $password = md5($this->post['password']);
//            $cookietime = intval($this->post['cookietime']);
//            $forward = isset($this->post['forward']) ? $this->post['forward'] : SITE_URL;
//            //ucenter登录成功，则不会继续执行后面的代码。
//            if ($this->setting["ucenter_open"]) {
//                $this->load('ucenter');
//                $_ENV['ucenter']->login($username, $password);
//            }
//            
//            
//           // $this->setting['code_login'] && $this->checkcode(); //检查验证码
//            $user = $_ENV['user']->get_by_username($username);
//            if (is_array($user) && ($password == $user['password'])) {
//            	if($user['isblack']==1){
//        		
//        		 $this->message($username.'用户被列入网站黑名单!', 'user/login');
//        	}
//                $_ENV['user']->refresh($user['uid'], 1, $cookietime);
//                $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward);
//                $this->credit($this->user['uid'], $this->setting['credit1_login'], $this->setting['credit2_login']); //登录增加积分
//                header("Location:" . $forward);
//            } else {
//                $this->message('用户名或密码错误！', 'user/login');
//            }
//        } else {
        	
       
            $forward = isset($_SERVER['HTTP_REFERER'])  ? $_SERVER['HTTP_REFERER'] : SITE_URL;
              
            include template('login');
        //}
    }

    /* 用于ajax登录 */

    function onajaxlogin() {
    	
        session_start();
   
        $username = $this->post['username'];
        if (ASK2_CHARSET == 'GBK') {
            require_once(ASK2_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $password = md5($this->post['password']);
    //ucenter登录成功，则不会继续执行后面的代码。
            if ($this->setting["ucenter_open"]) {
                $this->load('ucenter');
                $_ENV['ucenter']->login($username, $password);
            }
            
        $user = $_ENV['user']->get_by_username($username);
          $cookietime =2592000;
        if (is_array($user) && ($password == $user['password'])) {
        	if($user['isblack']==1){
        		
        		  exit('-1');
        	}
        	 $_ENV['user']->refresh($user['uid'], 1, $cookietime);
            exit('1');
        }
        exit('-1');
    }

    /* 用于ajax检测用户名是否存在 */

    function onajaxusername() {
        $username = $this->post['username'];
        if (ASK2_CHARSET == 'GBK') {
            require_once(ASK2_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user)
        )
            exit('-1');
        $usernamecensor = $_ENV['user']->check_usernamecensor($username);
        if (FALSE == $usernamecensor)
            exit('-2');
        exit('1');
    }
    /* 用于ajax检测用户名是否存在 */

    function onajaxupdateusername() {
    	
    	if($this->user['uid']==0){
    		exit('0');
    	}
        $username = $this->post['username'];
        if (ASK2_CHARSET == 'GBK') {
            require_once(ASK2_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user)
        )
            exit('-1');
        $usernamecensor = $_ENV['user']->check_usernamecensor($username);
        if (FALSE == $usernamecensor)
            exit('-2');
      
        $useremail = $this->post['useremail']; 
       $emailaccess = $_ENV['user']->check_emailaccess($useremail);
        if (FALSE == $emailaccess
        ){
        	exit("-3");
        }
          $user = $_ENV['user']->get_by_email($useremail);
        if (is_array($user)){
        	 exit('-4');
        }
        
           
        //更新用户名
        $_ENV['user']->update_username($this->user['uid'],$username,$useremail);
        
        //发送邮件确认
               $sitename=$this->setting['site_name'];
    		    $activecode=md5(rand(10000,50000));
    			      $url=SITE_URL.'index.php?user/checkemail/'.$this->user['uid'].'/'.$activecode;
    			    $message="这是一封来自$sitename邮箱验证，<a target='_blank' href='$url'>请点击此处验证邮箱邮箱账号</a>";
    			    $v=md5("yanzhengask2email");
    			    $v1=md5("yanzhengask2time");
    			    setcookie("emailsend");
    			    setcookie("useremailcheck");
    			     $expire1 = time() + 20; // 设置1分钟的有效期
                    setcookie ("emailsend",  $v1, $expire1); // 设置一个名字为var_name的cookie，并制定了有效期
    			    $expire = time() + 86400; // 设置24小时的有效期
                    setcookie ("useremailcheck",  $v, $expire); // 设置一个名字为var_name的cookie，并制定了有效期
                    $_ENV['user']->update_emailandactive($useremail,$activecode,$this->user['uid']);
    			    $_ENV['user']->refresh($this->user['uid'],1);
    				sendmailto($useremail, "邮箱验证提醒-$sitename", $message,$this->user['username']);
    				
    				
        exit('1');
    }

    
    function onajaxpopwxpay(){
    	
    	 $type=htmlspecialchars($this->get[2]);
    	 	 $typevalue=htmlspecialchars($this->get[3]);
    	 	  $touser=htmlspecialchars($this->get[4]);
    	 	  
    	  include template("wxpay");
    }
    function onajaxgetpaycode(){
    	 $t1=htmlspecialchars($this->post['type']);
    	  $t2=htmlspecialchars($this->post['typevalue']);
    	   $t3=htmlspecialchars($this->post['touser']);
    	    $t4=htmlspecialchars($this->post['money']);
    	    
    	    $t5=rand(111111111, 999999999);
    	require_once ASK2_ROOT."/lib/wxpay/lib/WxPay.Api.php";
     require_once    ASK2_ROOT."/lib/wxpay/WxPay.NativePay.php";
      require_once  ASK2_ROOT.'/lib/wxpay/log.php';
      $notify = new NativePay();
              $url1 = $notify->GetPrePayUrl($t1."_".$t2."_".$t3."_".$t4."_".$t5);
              echo urlencode($url1);
    }

    /* 用于ajax检测用户名是否存在 */

    function onajaxemail() {
        $email = $this->post['email'];
        $user = $_ENV['user']->get_by_email($email);
        if (is_array($user)
        )
            exit('-1');
        $emailaccess = $_ENV['user']->check_emailaccess($email);
        if (FALSE == $emailaccess
        )
            exit('-2');
        exit('1');
    }

    /* 用于ajax检测验证码是否匹配 */

    function onajaxcode() {
        $code = strtolower(trim($this->get[2]));
        if ($code == $_ENV['user']->get_code()) {
            exit('1');
        }
        exit('0');
    }
      /* 用于ajax设置用户提问金额 */
 function onajaxsetmypay(){
 	
 	$uid=$this->user['uid'];
 	
 	$mypay=floatval($this->post['mypay']);
 	if($uid==0){
 		exit("-1");
 	}
     $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `mypay`='$mypay' WHERE `uid`=$uid");
      exit("1");
 
 }
 
    /* 退出系统 */

    function onlogout() {
        $navtitle = '登出系统';
        //ucenter退出成功，则不会继续执行后面的代码。
        if ($this->setting["ucenter_open"]) {
            $this->load('ucenter');
            $_ENV['ucenter']->ajaxlogout();
        }
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
        $_ENV['user']->logout();
        $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward); //通行证处理
        $this->message('成功退出！',"index");
    }

    /* 找回密码 */

    function ongetpass() {
        $navtitle = '找回密码';
        if (isset($this->post['submit'])) {
            $email = $this->post['email'];
            $name = $this->post['username'];
            //$this->checkcode(); //检查验证码
           if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
            $touser = $_ENV['user']->get_by_name_email($name, $email);
            if ($touser) {
            	 $activecode=md5(rand(10000,50000));
    			      $getpassurl=SITE_URL.'index.php?user/resetpass/'.encode($touser['uid']).'/'.$activecode;
    			  
    			    
                    $_ENV['user']->update_emailandactive($email,$activecode,$touser['uid']);
                    
              
            
               
                $subject = "找回您在" . $this->setting['site_name'] . "的密码";
                $message = '<p>如果是您在<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>的密码丢失，请点击下面的链接找回：</p><p><a swaped="true" target="_blank" href="' . $getpassurl . '">' . $getpassurl . '</a></p><p>如果直接点击无法打开，请复制链接地址，在新的浏览器窗口里打开。</p>';
                sendmail($touser, $subject, $message);
                $this->message("找回密码的邮件已经发送到你的邮箱，请查收!", 'BACK');
            }
            $this->message("用户名或邮箱填写错误，请核实!", 'BACK');
        }
        include template('getpass');
    }

    /* 重置密码 */

    function onresetpass() {
    	if($this->user['uid']>0){
    		 $this->message("您已经登录了!");
    	}
        $navtitle = '重置密码';
            $uid=intval(decode($this->get[2]));
    	 	$activecode=strip_tags($this->get[3]);
    	 	$user= $_ENV['user']->get_by_uid($uid);
    	      
    	 	if($user['activecode']==$activecode){
    	 		$_ENV['user']->update_useractive($uid);
    	 		 
    	 	}else{
    	 		$this->message("非法操作!");
    	 	}
    	 	$authcode=$this->get[2];
        if (isset($this->post['submit'])) {
            $password = $this->post['password'];
            $repassword = $this->post['repassword'];
            $uid=decode($this->post['authcode']);
            if (strlen($password) < 6) {
                $this->message("密码长度不能少于6位!", 'BACK');
            }
            if ($password != $repassword) {
                $this->message("两次密码输入不一致!", 'BACK');
            }
            $_ENV['user']->uppass($uid, $password);
            $_ENV['user']->update_authstr($uid, '');
            $this->message("重置密码成功，请使用新密码登录!");
        }
        include template('resetpass');
    }

    function onask() {
        $navtitle = '我的问题';
        $status = intval($this->get[2]);
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $questionlist = $_ENV['question']->list_by_uid($this->user['uid'], $status, $startindex, $pagesize);
        $questiontotal = intval($this->db->fetch_total('question', 'authorid=' . $this->user['uid'] . $_ENV['question']->statustable[$status]));
        $departstr = page($questiontotal, $pagesize, $page, "user/ask/$status"); //得到分页字符串
        include template('myask');
    }

    function onrecommend() {
        $this->load('message');
        $navtitle = '为我推荐的问题';
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $user_categorys = array_per_fields($this->user['category'], 'cid');
        $_ENV['message']->read_user_recommend($this->user['uid'], $user_categorys);
        $questionlist = $_ENV['message']->list_user_recommend($this->user['uid'], $user_categorys, $startindex, $pagesize);
        $questiontotal = $_ENV['message']->rownum_user_recommend($this->user['uid'], $user_categorys);
        $departstr = page($questiontotal, $pagesize, $page, "user/recommend");
        include template('myrecommend');
    }

    function onspace_ask() {
      
        $uid = intval($this->get[2]);
        $member = $_ENV['user']->get_by_uid($uid, 0);
        $navtitle = $member['username'].'的提问';
       $seo_description= $member['username'].'，'.$member['introduction'].'，'.$member['signature'];
             $seo_keywords= $member['username'];
        $status = $this->get[3] ? $this->get[3] : 'all';
        //升级进度
        $membergroup = $this->usergroup[$member['groupid']];
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $questionlist = $_ENV['question']->list_by_uid($uid, $status, $startindex, $pagesize);
       // print_r($questionlist);
       // exit();
        $questiontotal = $this->db->fetch_total('question', 'authorid=' . $uid . $_ENV['question']->statustable[$status]);
        $departstr = page($questiontotal, $pagesize, $page, "user/space_ask/$uid/$status"); //得到分页字符串
        include template('space_ask');
    }

    function onanswer() {
        $navtitle = '我的回答';
        $status = intval($this->get[2]);
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $answerlist = $_ENV['answer']->list_by_uid($this->user['uid'], $status, $startindex, $pagesize);
        $answersize = intval($this->db->fetch_total('answer', 'authorid=' . $this->user['uid'] . $_ENV['answer']->statustable[$status]));
        $departstr = page($answersize, $pagesize, $page, "user/answer/$status"); //得到分页字符串
        include template('myanswer');
    }

    function onspace_answer() {
       
        $uid = intval($this->get[2]);
        $status = $this->get[3] ? $this->get[3] : 'all';
        $member = $_ENV['user']->get_by_uid($uid, 0);
         $navtitle = $member['username'].'的回答';
          $seo_description= $member['username'].'，'.$member['introduction'].'，'.$member['signature'];
             $seo_keywords= $member['username'];
        //升级进度
        $membergroup = $this->usergroup[$member['groupid']];
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $answerlist = $_ENV['answer']->list_by_uid($uid, $status, $startindex, $pagesize);
        $answersize = intval($this->db->fetch_total('answer', 'authorid=' . $uid . $_ENV['answer']->statustable[$status]));
        $departstr = page($answersize, $pagesize, $page, "user/space_answer/$uid/$status"); //得到分页字符串
        include template('space_answer');
    }

    function onfollower() {
        $navtitle = '关注者';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $followerlist = $_ENV['user']->get_follower($this->user['uid'], $startindex, $pagesize);
        $rownum = $this->db->fetch_total('user_attention', " uid=" . $this->user['uid']);
        $departstr = page($rownum, $pagesize, $page, "user/follower");
        include template("myfollower");
    }
   function onspacefollower() {
   	
       
        $uid=intval($this->get[2]);
         $member = $_ENV['user']->get_by_uid($uid, 0);
          $navtitle =$member['username'].'的粉丝';
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $followerlist = $_ENV['user']->get_follower($uid, $startindex, $pagesize);
        $rownum = $this->db->fetch_total('user_attention', " uid=" . $uid);
        $departstr = page($rownum, $pagesize, $page, "user/spacefollower");
        include template("space_follower");
    }

    function onattention() {
        $navtitle = '已关注';
        $attentiontype = isset($this->get[2]) ? $this->get[2]:'';
        if ($attentiontype=='question') {
        	 $navtitle = '已关注问题';
            $page = max(1, intval($this->get[3]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $questionlist = $_ENV['user']->get_attention_question($this->user['uid'], $startindex, $pagesize);
            $rownum = $_ENV['user']->rownum_attention_question($this->user['uid']);
            $departstr = page($rownum, $pagesize, $page, "user/attention/$attentiontype");
            include template("myattention_question");
        }else if($attentiontype=='topic'){
        	
        	 $navtitle = '已关注话题';
        	   $page = max(1, intval($this->get[3]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $categorylist = $_ENV['user']->get_attention_category($this->user['uid'], $startindex, $pagesize);
            $rownum = $_ENV['user']->rownum_attention_category($this->user['uid']);
            $departstr = page($rownum, $pagesize, $page, "user/attention/$attentiontype");
            include template("myattention_category");
        }
         else {
         	 $navtitle = '已关注用户';
            $page = max(1, intval($this->get[2]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $attentionlist = $_ENV['user']->get_attention($this->user['uid'], $startindex, $pagesize);
            $rownum = $this->db->fetch_total('user_attention', " followerid=" . $this->user['uid']);
            $departstr = page($rownum, $pagesize, $page, "user/attention");
            include template("myattention");
        }
    }
    function onspace_attention(){
    	   $navtitle = '已关注';
     $attentiontype = isset($this->get[2]) ? $this->get[2]:'';
      $uid = intval($this->get[3]);
            $member = $_ENV['user']->get_by_uid($uid, 0);
        if ($attentiontype=='question') {
        	 $navtitle = '已关注问题';
            $page = max(1, intval($this->get[4]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $questionlist = $_ENV['user']->get_attention_question($uid, $startindex, $pagesize);
            $rownum = $_ENV['user']->rownum_attention_question($uid);
            $departstr = page($rownum, $pagesize, $page, "user/attention/$attentiontype");
            include template("space_myattention_question");
        }else if($attentiontype=='topic'){
        	
        	 $navtitle = '已关注话题';
        	   $page = max(1, intval($this->get[4]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $categorylist = $_ENV['user']->get_attention_category($uid, $startindex, $pagesize);
            $rownum = $_ENV['user']->rownum_attention_category($uid);
            $departstr = page($rownum, $pagesize, $page, "user/attention/$attentiontype");
            include template("space_myattention_category");
        }
         else {
         	 $navtitle = '已关注用户';
            $page = max(1, intval($this->get[4]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $attentionlist = $_ENV['user']->get_attention($uid, $startindex, $pagesize);
            $rownum = $this->db->fetch_total('user_attention', " followerid=" . $uid);
            $departstr = page($rownum, $pagesize, $page, "user/attention");
            include template("space_myattention");
        }
    }

    function onscore() {
        $navtitle = '我的个人中心';
        if ($this->setting['outextcredits']) {
            $outextcredits = unserialize($this->setting['outextcredits']);
        }
        $higherneeds = intval($this->user['creditshigher'] - $this->user['credit1']);
        $adoptpercent = $_ENV['user']->adoptpercent($this->user);
        $highergroupid = $this->user['groupid'] + 1;
        isset($this->usergroup[$highergroupid]) && $nextgroup = $this->usergroup[$highergroupid];
        $credit_detail = $_ENV['user']->credit_detail($this->user['uid']);
        $detail1 = $credit_detail[0];
        $detail2 = $credit_detail[1];
        
        $status = 'all';
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $userid=$this->user['uid'];
          $this->load('doing');
        $doinglist = $_ENV['doing']->list_by_type("my", $userid, $startindex, $pagesize);
            $rownum = $_ENV['doing']->rownum_by_type("my", $userid);
            
         
              
            $departstr = page($rownum, $pagesize, $page, "user/score/$userid");
             	   $answerlist = $_ENV['answer']->list_by_uid($userid, 'all', $startindex, $pagesize);
             	   $questionlist = $_ENV['question']->list_by_uid($userid, 'all', $startindex, $pagesize);
             	       $topiclist = $_ENV['topic']->get_list_byuid($userid, $startindex, $pagesize);
             	    $followerlist = $_ENV['user']->get_follower($userid, $startindex, $pagesize);
            $attentionlist = $_ENV['user']->get_attention($userid, $startindex, $pagesize);
           
       
         	 include template('myscore');
         
       
    }

    function onlevel() {
        $navtitle = '我的等级';
        $usergroup = $this->usergroup;
        include template("mylevel");
    }

    function onexchange() {
        $navtitle = '积分兑换';
        if ($this->setting['outextcredits']) {
            $outextcredits = unserialize($this->setting['outextcredits']);
        } else {
            $this->message("系统没有开启积分兑换!", 'BACK');
        }
        $exchangeamount = $this->post['exchangeamount']; //先要兑换的积分数
        $outextindex = $this->post['outextindex']; //读取相应积分配置
        $outextcredit = $outextcredits[$outextindex];
        $creditsrc = $outextcredit['creditsrc']; //积分兑换的源积分编号
        $appiddesc = $outextcredit['appiddesc']; //积分兑换的目标应用程序 ID
        $creditdesc = $outextcredit['creditdesc']; //积分兑换的目标积分编号
        $ratio = $outextcredit['ratio']; //积分兑换比率
        $needamount = $exchangeamount / $ratio; //需要扣除的积分数

        if ($needamount <= 0) {
            $this->message("兑换的积分必需大于0 !", 'BACK');
        }
        if (1 == $creditsrc) {
            $titlecredit = '经验值';
            if ($this->user['credit1'] < $needamount) {
                $this->message("{$titlecredit}不足!", 'BACK');
            }
            $this->credit($this->user['uid'], -$needamount, 0, 0, 'exchange'); //扣除本系统积分
        } else {
            $titlecredit = '财富值';
            if ($this->user['credit2'] < $needamount) {
                $this->message("{$titlecredit}不足!", 'BACK');
            }
            $this->credit($this->user['uid'], 0, -$needamount, 0, 'exchange'); //扣除本系统积分
        }
        $this->load('ucenter');
        $_ENV['ucenter']->exchange($this->user['uid'], $creditsrc, $creditdesc, $appiddesc, $exchangeamount);
        $this->message("积分兑换成功!  你在“{$this->setting[site_name]}”的{$titlecredit}减少了{$needamount}。");
    }

    /* 个人中心修改资料 */

    function onprofile() {
        $navtitle = '个人资料';
        if (isset($this->post['submit'])) {
            $gender = $this->post['gender'];
            $bday = $this->post['birthyear'] . '-' . $this->post['birthmonth'] . '-' . $this->post['birthday'];
            $phone = $this->post['phone'];
            $qq = $this->post['qq'];
            $msn = $this->post['msn'];
            $messagenotify = isset($this->post['messagenotify']) ? 1 : 0;
            $mailnotify = isset($this->post['mailnotify']) ? 2 : 0;
            $isnotify = $messagenotify + $mailnotify;
            $introduction = htmlspecialchars($this->post['introduction']);
            $signature = htmlspecialchars($this->post['signature']);
          $userone=$_ENV['user']->get_by_phone($phone);
          if(trim($phone)!=''){//是否为空
           if($userone!=null&&$userone['uid']!=$this->user['uid']){//不为空且不是本人号码
             $this->message("手机号码已存在!", 'user/profile');
            }
          }
           
            if (($this->post['email'] != $this->user['email']) && (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $this->post['email']) || $this->db->fetch_total('user', " email='" . $this->post['email'] . "' "))) {
                $this->message("邮件格式不正确或已被占用!", 'user/profile');
            }
            $_ENV['user']->update($this->user['uid'], $gender, $bday, $phone, $qq, $msn, $introduction, $signature, $isnotify);
            isset($this->post['email']) && $_ENV['user']->update_email($this->post['email'], $this->user['uid']);
            $this->message("个人资料更新成功", 'user/profile');
        }
        include template('profile');
    }

    function onuppass() {
       // $this->load("ucenter");
        $navtitle = "修改密码";
        if (isset($this->post['submit'])) {
        				   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
            if (trim($this->post['newpwd']) == '') {
                $this->message("新密码不能为空！", 'user/uppass');
            } else if (trim($this->post['newpwd']) != trim($this->post['confirmpwd'])) {
                $this->message("两次输入不一致", 'user/uppass');
            } else if (trim($this->post['oldpwd']) == trim($this->post['newpwd'])) {
                $this->message('新密码不能跟当前密码重复!', 'user/uppass');
            } else if (md5(trim($this->post['oldpwd'])) == $this->user['password']) {
            	  if ($this->setting["ucenter_open"]) {
            	  		$this->load("ucenter");
            	$_ENV['ucenter']->uppass($this->user['username'], $this->post['oldpwd'], $this->post['newpwd'], $this->user['email']);
                
            	  }
            
            	$_ENV['user']->uppass($this->user['uid'], trim($this->post['newpwd']));
                $this->message("密码修改成功,请重新登录系统!", 'user/login');
            } else {
                $this->message("旧密码错误！", 'user/uppass');
            }
        }
        include template('uppass');
    }

    // 1提问  2回答
    function onspace() {
        $navtitle = "个人空间";
        $userid = intval($this->get[2]);
        $member = $_ENV['user']->get_by_uid($userid, 2);
        if ($member) {
            $this->load('doing');
            $membergroup = $this->usergroup[$member['groupid']];
            $adoptpercent = $_ENV['user']->adoptpercent($member);
            $page = max(1, intval($this->get[3]));
            $pagesize = 15;
            $startindex = ($page - 1) * $pagesize;
            $doinglist = $_ENV['doing']->list_by_type("my", $userid, $startindex, $pagesize);
            $rownum = $_ENV['doing']->rownum_by_type("my", $userid);
            
            $is_followed = $_ENV['user']->is_followed($member['uid'], $this->user['uid']);
              
            $departstr = page($rownum, $pagesize, $page, "user/space/$userid");
            
             
             	   $answerlist = $_ENV['answer']->list_by_uid($userid, 'all', $startindex, $pagesize);
             	   $questionlist = $_ENV['question']->list_by_uid($userid, 'all', $startindex, $pagesize);
             	       $topiclist = $_ENV['topic']->get_list_byuid($userid, $startindex, $pagesize);
             	    $followerlist = $_ENV['user']->get_follower($userid, $startindex, $pagesize);
            $attentionlist = $_ENV['user']->get_attention($userid, $startindex, $pagesize);
          
            $navtitle = $member['username'] . $navtitle;
           $seo_description= $member['username'].'，'.$member['introduction'].'，'.$member['signature'];
             $seo_keywords= $member['username'];
            include template('space');
        } else {
            $this->message("抱歉，该用户个人空间不存在！", 'BACK');
        }
    }

    // 0总排行、1上周排行 、2上月排行
    //user/scorelist/1/
    function onscorelist() {
        $navtitle = "乐帮排行榜";
        $seo_description= "乐帮排行榜展示问答最活跃的用户列表，包括达人财富榜，并推荐最新文章和关注问题排行榜。";
        $seo_keywords= "活跃用户,达人财富,最新文章推荐,关注问题排行榜";
        $type = isset($this->get[2]) ? $this->get[2] : 0;
        $userlist = $_ENV['user']->list_by_credit($type, 100);
        
         $useractivelistlist = $_ENV['user']->get_active_list(0, 6);
        $usercount = count($userlist);
        include template('scorelist');
    }

    function onactivelist() {
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $userlist = $_ENV['user']->get_active_list($startindex, $pagesize);
        $answertop = $_ENV['user']->get_answer_top();
        $rownum = $this->db->fetch_total('user', " 1=1 ");
        $departstr = page($rownum, $pagesize, $page, "user/activelist");
        if($page==1){
        $navtitle = "站点用户活跃度列表";	
        }else{
        	$navtitle = "站点用户列表"."_第".$page."页";
        }
          
        $seo_description= "站点用户列表，根据用户活跃度展示用户排序。";
        $seo_keywords= "站点用户列表";
        include template("activelist");
    }
    function oncheckemail(){
    	
    	// if(isset($_COOKIE["useremailcheck"])){
    	 	
    	 	$uid=intval($this->get[2]);
    	 	$activecode=strip_tags($this->get[3]);
    	 	$user= $_ENV['user']->get_by_uid($uid);
    	 	if($user['active']==1){
    	 		 $_ENV['user']->logout();
    	 		 $this->message("您的邮箱已激活过，请勿重复激活!",'index');
    	 	}
    	
      
        $_ENV['user']->logout();
       

    	 	if($user['activecode']==$activecode){
    	 	  //if ($this->setting["ucenter_open"]) {
            	  		//$this->load("ucenter");
            	///$_ENV['ucenter']->uppass($user['username'], $user['password'], $user['password'], $user['email'],1);
                
            	  //}
    	 		$_ENV['user']->update_useractive($uid);
    	 		 $this->message("邮箱激活成功!",'index');
    	 	}else{
    	 		$this->message("邮箱激活失败!",'index');
    	 	}
    	// }else{
    	 //	$this->message("邮箱激活已经过期!");
    	// }
    	
    }
    //发送邮件验证
    function  onsendcheckmail() {
  
    	if($this->user['uid']>0){
    		if($this->user['active']==1){
    			exit("您激活过邮箱了,您是不是想修改邮箱!");
    		}
    		if($_COOKIE['emailsend']!=null){
    			exit("已发送过激活邮箱，请1分钟之后再试，不要恶意发送!");
    		}
    		$email=$this->user['email'];
    		if(isset($this->user['email'])&&$this->user['email']!=""){
    			$sitename=$this->setting['site_name'];
    			
    			
    			
    			//if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
    				
    			    $activecode=md5(rand(10000,50000));
    			    $url=SITE_URL.'index.php?user/checkemail/'.$this->user['uid'].'/'.$activecode;
    			    $message="这是一封来自$sitename邮箱验证，<a target='_blank' href='$url'>请点击此处验证邮箱邮箱账号</a>";
    			    $v=md5("yanzhengask2email");
    			    $v1=md5("yanzhengask2time");
    			     setcookie("emailsend");
    			    setcookie("useremailcheck");
    			    setcookie("emailsend","OKadmin",time()-1);
setcookie("emailsend","OKadmin",0); //浏览器关闭 是自动失效
    setcookie("useremailcheck","OKadmin",time()-1);
setcookie("useremailcheck","OKadmin",0); //浏览器关闭 是自动失效
    			     $expire1 = time() + 60; // 设置1分钟的有效期
                    setcookie ("emailsend",  $v1, $expire1); // 设置一个名字为var_name的cookie，并制定了有效期
    			    $expire = time() + 86400; // 设置24小时的有效期
                    setcookie ("useremailcheck",  $v, $expire); // 设置一个名字为var_name的cookie，并制定了有效期
                    $_ENV['user']->update_emailandactive($email,$activecode,$this->user['uid']);
    			    $_ENV['user']->refresh($this->user['uid'],1);
    				sendmailto($email, "邮箱验证提醒-$sitename", $message,$this->user['username']);
    			exit("邮箱验证发送成功，24小时之内请进行邮箱验证，在您没激活邮件之前你不能发布问题和文章等操作！");
    			
    			//}else{
    				//exit("网站还没做邮箱配置或者开启邮箱注册!");
    			//}
    		}else{
    			exit("您还没设置过邮箱，请先使用修改邮箱功能!");
    		}
    	}else{
    		exit("您还没登陆!");
    	}
    	
    }
    
    //邮箱激活验证
    function onvertifyemail(){
    	//验证是否登录
       if($this->user['uid']==0){
    		$this->message("您还没登陆！", 'index');
    	}
    	//验证是否设置过邮箱
        if(trim($this->user['email'])==''||!isset($this->user['email'])){
    		$this->message("您还没设置过邮箱！", 'user/editemail');
    	}
    	
      if($this->user['active']==1){
    		$this->message("您的邮箱已经激活过！", 'index');
    	}
    	
       if($this->user['activecode']==''||$this->user['activecode']==0||$this->user['activecode']==null){
    		$sitename=$this->setting['site_name'];
    	 $email=$this->user['email'];
    			    $activecode=md5(rand(10000,50000));
    			      $url=SITE_URL.'index.php?user/checkemail/'.$this->user['uid'].'/'.$activecode;
    			    $message="这是一封来自$sitename邮箱验证，<a target='_blank' href='$url'>请点击此处验证邮箱邮箱账号</a>";
    			    $v=md5("yanzhengask2email");
    			    $v1=md5("yanzhengask2time");
    			    setcookie("emailsend");
    			    setcookie("useremailcheck");
    			     $expire1 = time() + 60; // 设置1分钟的有效期
                    setcookie ("emailsend",  $v1, $expire1); // 设置一个名字为var_name的cookie，并制定了有效期
    			    $expire = time() + 86400; // 设置24小时的有效期
                    setcookie ("useremailcheck",  $v, $expire); // 设置一个名字为var_name的cookie，并制定了有效期
                    $_ENV['user']->update_emailandactive($email,$activecode,$this->user['uid']);
    			    $_ENV['user']->refresh($this->user['uid'],1);
    				sendmailto($email, "邮箱验证提醒-$sitename", $message,$this->user['username']);
    			
    	}
    	 include template("vertifyemail");
    	
    	 
    }
    /*
     * 
     * 修改邮箱
     */
    function oneditemail() {
  
  
    	if($this->user['uid']==0){
    		$this->message("您还没登陆！", 'BACK');
    	}
    	
        		
    	session_start();
    	if($this->post['submit']){
    		
    	      		   if (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code()) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
        			
        	
    		$email=trim($this->post['email']);
    		if(empty($email)){
    			$this->message("抱歉，邮箱不能为空！", 'BACK');
    		}
    		 	 $emailaccess = $_ENV['user']->check_emailaccess($email);
        if (FALSE == $emailaccess){
        	 $this->message("邮箱后缀被系统列入黑名单，禁止注册!","BACK");
        }
         $euser = $_ENV['user']->get_by_email($email);
        if (is_array($euser)
        ){
        	 $this->message("此邮箱已经被注册了!","BACK");
        }
        
    		if($this->user['email']!=$email){
    			
    			$sitename=$this->setting['site_name'];
    			
    			
    			
    			if(isset($this->setting['register_on'])&&$this->setting['register_on']=='1'){
    				
    			    $activecode=md5(rand(10000,50000));
    			      $url=SITE_URL.'index.php?user/checkemail/'.$this->user['uid'].'/'.$activecode;
    			    $message="这是一封来自$sitename邮箱验证，<a target='_blank' href='$url'>请点击此处验证邮箱邮箱账号</a>";
    			    $v=md5("yanzhengask2email");
    			    $v1=md5("yanzhengask2time");
    			    setcookie("emailsend");
    			    setcookie("useremailcheck");
    			     $expire1 = time() + 60; // 设置1分钟的有效期
                    setcookie ("emailsend",  $v1, $expire1); // 设置一个名字为var_name的cookie，并制定了有效期
    			    $expire = time() + 86400; // 设置24小时的有效期
                    setcookie ("useremailcheck",  $v, $expire); // 设置一个名字为var_name的cookie，并制定了有效期
                    $_ENV['user']->update_emailandactive($email,$activecode,$this->user['uid']);
    			    $_ENV['user']->refresh($this->user['uid'],1);
    				sendmailto($email, "邮箱验证提醒-$sitename", $message,$this->user['username']);
    			
    			 $this->message("邮箱验证发送成功，24小时之内请进行邮箱验证，在您没激活邮件之前你不能发布问题和文章等操作！", 'BACK');
    			}else{
    				$_ENV['user']->update_email($email,$this->user['uid']);
    			    $_ENV['user']->refresh($this->user['uid'],1);
    				$this->message("邮箱修改成功，站长没有配置邮箱验证", 'BACK');
    			}
    			
    		}
    		
    	}
    	$_SESSION["formkey"]= getRandChar(56);
    	  include template("editemail");
    }
    function oneditimg() {
        if (isset($_FILES["userimage"])) {
            $uid = intval($this->get[2]);
            
          
            $avatardir = "/data/avatar/";
            $extname = extname($_FILES["userimage"]["name"]);
            if (!isimage($extname))
                $this->message("图片扩展名不正确!", 'user/editimg');
            $upload_tmp_file = ASK2_ROOT . '/data/tmp/user_avatar_' . $uid . '.' . $extname;
            $uid = abs($uid);
            $uid = sprintf("%09d", $uid);
            $dir1 = $avatardir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 2);
            $dir3 = $dir2 . '/' . substr($uid, 5, 2);
            (!is_dir(ASK2_ROOT . $dir1)) && forcemkdir(ASK2_ROOT . $dir1);
            (!is_dir(ASK2_ROOT . $dir2)) && forcemkdir(ASK2_ROOT . $dir2);
            (!is_dir(ASK2_ROOT . $dir3)) && forcemkdir(ASK2_ROOT . $dir3);
            $smallimg = $dir3 . "/small_" . $uid . '.' . $extname;
            if (move_uploaded_file($_FILES["userimage"]["tmp_name"], $upload_tmp_file)) {
                $avatar_dir = glob(ASK2_ROOT . $dir3 . "/small_{$uid}.*");
                foreach ($avatar_dir as $imgfile) {
                    if (strtolower($extname) != extname($imgfile))
                        unlink($imgfile);
                }
               image_resize($upload_tmp_file, ASK2_ROOT . $smallimg, 85, 85,1);
                   
            }
        } else {
            if ($this->setting["ucenter_open"]) {
                $this->load('ucenter');
                $imgstr = $_ENV['ucenter']->set_avatar($this->user['uid']);
            }
           
        }
        
         include template("editimg");
    }

    function onmycategory() {
        $this->load("category");
        $categoryjs = $_ENV['category']->get_js();
        $qqlogin = $_ENV['user']->get_login_auth($this->user['uid'], 'qq');
        $sinalogin = $_ENV['user']->get_login_auth($this->user['uid'], 'sina');
        include template("mycategory");
    }

    //解除绑定
    function onunchainauth() {
        $type = ($this->get[2] == 'qq') ? 'qq' : 'sina';
        $_ENV['user']->remove_login_auth($this->user['uid'], $type);
        $this->message($type . "绑定解除成功!", 'user/mycategory');
    }

    function onajaxcategory() {
        $cid = intval($this->post['cid']);
        if ($cid && $this->user['uid']) {
            foreach ($this->user['category'] as $category) {
                if ($category['cid'] == $cid) {
                    exit;
                }
            }
            $_ENV['user']->add_category($cid, $this->user['uid']);
        }
    }

    function onajaxdeletecategory() {
        $cid = intval($this->post['cid']);
        if ($cid && $this->user['uid']) {
            $_ENV['user']->remove_category($cid, $this->user['uid']);
        }
    }

    function onajaxpoplogin() {
    	session_start();
    	$_SESSION["apikey"]=null;
    	 $_SESSION["userid"]= getRandChar(56);
    	  $_SESSION["apikey"]= getRandChar(56);
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        include template("poplogin");
    }

    /* 用户查看下详细信息 */

    function onajaxuserinfo() {
        $uid = intval($this->get[2]);
        if ($uid) {
            $userinfo = $_ENV['user']->get_by_uid($uid, 1);
            $is_followed = $_ENV['user']->is_followed($userinfo['uid'], $this->user['uid']);
            $userinfo_group = $this->usergroup[$userinfo['groupid']];
            include template("usercard");
        }
    }

    function onajaxloadmessage() {
        $uid = $this->user['uid'];
        if ($uid == 0) {
            return;
        }
       
        $user_categorys = array_per_fields($this->user['category'], 'cid');
        $message = array();
        $this->load('message');
        $message['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid=0 AND status<>2");
        $message['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>2");
        $message['message_recommand'] = $_ENV['message']->rownum_user_recommend($uid, $user_categorys, 'notread');
     ob_start() ;
        echo tjson_encode($message);
        ob_end_flush();
        exit;
    }

 

    //关注用户
    function onattentto() {
        $uid = intval($this->post['uid']);
        if (!$uid) {
            exit('error');
        }
   
        $is_followed = $_ENV['user']->is_followed($uid, $this->user['uid']);
        if ($is_followed) {
        	
            $_ENV['user']->unfollow($uid, $this->user['uid'], 'user');
              $this->load("doing");
             $_ENV['doing']->deletedoing($this->user['uid'],11,$uid);
        } else {
             if($uid==$this->user['uid']){
        	 exit('self');
        }
            $_ENV['user']->follow($uid, $this->user['uid'], $this->user['username'], 'user');
            $quser= $_ENV['user']->get_by_uid($uid);
                               $this->load("doing");
               $_ENV['doing']->add($this->user['uid'], $this->user['username'], 11, $uid, $quser['username']);
            $msgfrom = $this->setting['site_name'] . '管理员';
            $username = addslashes($this->user['username']);
            $this->load("message");
            $_ENV['message']->add($msgfrom, 0, $uid, $username . "刚刚关注了您", '<a target="_blank" href="' . url('user/space/' . $this->user['uid'], 1) . '">' . $username . '</a> 刚刚关注了您!<br /> <a href="' . url('user/follower', 1) . '">点击查看</a>');
        }
        exit('ok');
    }

}

?>