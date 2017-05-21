<?php

!defined('IN_ASK2') && exit('Access Denied');

class base {

    var $ip;
    var $time;
    var $db;
    var $cache;
    var $user = array();
    var $setting = array();
    var $category = array();
    var $usergroup = array();
    var $get = array();
    var $post = array();
    var $regular = array();
    var $statusarray = array('all' => '全部', '0' => '待审核', '1' => '待解决', '2' => '已解决', '4' => '悬赏', '9' => '已关闭');

    function base(& $get, & $post) {
    	
        $this->time = time();
        $this->ip = getip();
        $this->get = & $get;
        $this->post = & $post;
        $this->init_db();
        $this->init_cache();
        
        $this->init_user();
      //  $this->checkcode();
        $this->banned();
      
    }

    function init_db() {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }

    /* 一旦setting的缓存文件读取失败，则更新所有cache */

    function init_cache() {
        global $setting, $category, $badword,$keyword;
        $this->cache = new cache($this->db);
        $setting = $this->setting = $this->cache->load('setting');
        $category = $this->category = $this->cache->load('category', 'id', 'displayorder');
        $badword = $this->cache->load('badword', 'find');
         $keyword = $this->cache->load('keywords', 'find');
        $this->usergroup = $this->cache->load('usergroup', 'groupid');
    }

    /* 从缓存中读取数据，如果失败，则自动去读取数据然后写入缓存 */

    function fromcache($cachename, $cachetime = 3) {
        $cachetime = ($this->setting['index_life'] == 0) ? 1 : $this->setting['index_life'] * 60;
        if ($cachetime == 'static') {
            $cachedata = $this->cache->read($cachename, 0);
        } else {
            $cachedata = $this->cache->read($cachename, $cachetime);
        }

        if ($cachedata)
            return $cachedata;
        switch ($cachename) {
            case 'headernavlist':
                $this->load('nav');
                $cachedata = $_ENV['nav']->get_format_url();
                break;
              
            case 'nosolvelist': //待解决问题，网友正在问
                $this->load('question');
                $cachedata = $_ENV['question']->list_by_cfield_cvalue_status('', 0, 'all', 0, $this->setting['list_indexnosolve']);
                break;
            case 'solvelist'://已解决问题
                $this->load('question');
                $cachedata = $_ENV['question']->list_by_cfield_cvalue_status('', 0, 2, 0, $this->setting['list_indexnosolve']);
                break;
            case 'rewardlist'://悬赏的问题
                $this->load('question');
                $cachedata = $_ENV['question']->list_by_cfield_cvalue_status('', 0, 4, 0, $this->setting['list_indexreward']);
                break;
                case 'shangjinlist'://现金悬赏的问题
                $this->load('question');
                $cachedata = $_ENV['question']->list_by_shangjin( 0, $this->setting['list_indexreward']);
                break;
                   case 'yuyinlist'://现金悬赏的问题
                $this->load('question');
                $cachedata = $_ENV['question']->list_by_yuyin( 0, $this->setting['list_indexreward']);
                break;
            case 'attentionlist'://关注问题排行榜
                $this->load('question');
                $cachedata = $_ENV['question']->get_hots(0, 8);
                break;
            case 'weekuserlist'://达人飙升榜
                $this->load('user');
                $cachedata = $_ENV['user']->list_by_credit(1, $this->setting['list_indexweekscore']);
                break;
            case 'alluserlist'://总积分榜
                $this->load('user');
                $cachedata = $_ENV['user']->list_by_credit(0, $this->setting['list_indexallscore']);
                break;
            case 'hosttaglist'://热门标签
                $this->load("tag");
                $cachedata = $_ENV['tag']->get_list(0, $this->setting['list_indexhottag']);
                break;
            case 'categorylist'://首页左侧分类列表
                $this->load('category');
                $cachedata = $_ENV['category']->list_by_grade();
                break;
                   case 'topdata'://首页顶置缓存内容数组
                $this->load('topdata');
                 if(!isset($this->setting['list_topdatanum'])){
                 	 $cachedata = $_ENV['topdata']->get_list(0,3);
                 }else{
                 	 $cachedata = $_ENV['topdata']->get_list(0,$this->setting['list_topdatanum']);
                 }
               
                break;
                
            case 'notelist'://首页右侧公告列表
                $this->load('note');
                $cachedata = $_ENV['note']->get_list(0, 10);
                break;
            case 'statistics'://首页统计，包含已解决、待解决
                $this->load('question');
                $cachedata = array();
                $cachedata['solves'] = $this->db->fetch_total('question', 'status IN (2,6)');   //已解决问题数
                $cachedata['nosolves'] = $this->db->fetch_total('question', 'status=1'); //待解决问题数
                break;
                
            case 'doinglist'://首页动态
             $this->load('doing');
       
     
        $cachedata = $_ENV['doing']->list_by_type_cache('all', 0, 0, 6);
            	break;
            case 'topiclist':
                $this->load('topic');
                $cachedata = $_ENV['topic']->get_list(1, 0, 10, 10);
                break;
                  case 'cweixin':
                $this->load('weixin_setting');
                $cachedata = $_ENV['weixin_setting']->get();
                break;
                   case 'hottopiclist':
                $this->load('topic');
                $cachedata = $_ENV['topic']->get_hotlist(1, 0, 12, 12);
                break;
                 case 'topiclistinphone':
                $this->load('topic');
                $cachedata =  $_ENV['topic']->get_list_bywhere(2,5);
                break;
                 case 'waptopiclist':
                $this->load('topic');
                $cachedata = $_ENV['topic']->get_list(1, 0, 8, 8);
                break;
            case 'expertlist':
                $this->load('expert');
                $cachedata = $_ENV['expert']->get_list(0, 0, $this->setting['list_indexexpert']);
                break;
                  case 'link'://友情链接
                $this->load('link');
                $cachedata = $_ENV['link']->get_list();
                break;
                
            case 'newuser':
            	
        $pagesize = $this->setting['list_default'];
        $this->load('user');
         $cachedata  = $_ENV['user']->get_active_list(1, 8);
      
       
        
            	break;
            case 'onlineusernum':
                $this->load('user');
                $cachedata = $_ENV['user']->rownum_onlineuser();
                break;
            case 'allusernum':
                $this->load('user');
                $cachedata = $_ENV['user']->rownum_alluser();
                break;
            case 'adlist':
                $this->load("ad");
                $cachedata = $_ENV['ad']->get_list();
                break;
            case 'activeuser':
                $this->load('user');
                $cachedata = $_ENV['user']->get_active_list(0, 6);
                break;
                 case 'hotwords':
                 	 $this->load('setting');
                 $cachedata =unserialize( $_ENV['setting']->get_hot_words($this->setting['list_hot_words']));
                break;
            case 'articlelist':
                if (isset($this->setting['cms_open']) && $this->setting['cms_open'] == 1) {
                    $this->load("cms");
                    $cachedata = $_ENV['cms']->get_list();
                } else {
                    $cachedata = array();
                }

                break;
        }
        $this->cache->write($cachename, $cachedata);
        return $cachedata;
    }

    function init_crontab() {
        $this->load('crontab');
        $crontablist = $this->cache->load("crontab");
        foreach ($crontablist as $crontab) {
            $crontab['available'] && $_ENV['crontab']->$crontab['method']($crontab);
        }
    }

    function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require ASK2_APP_ROOT . "/model/$model.class.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base);');
        }
        return $_ENV[$model];
    }

    function init_user() {
        @$sid = tcookie('sid');
        @$auth = tcookie('auth');
        $user = array();
        @list($uid, $password) = empty($auth) ? array(0, 0) : taddslashes(explode("\t", authcode($auth, 'DECODE')), 1);
        if (!$sid) {
            $sid = substr(md5(time() . $this->ip . random(6)), 16, 16);
            tcookie('sid', $sid, 31536000);
        }
        $this->load('user');
        if ($uid && $password) {
            $user = $_ENV['user']->get_by_uid($uid, 0);
            ($password != $user['password']) && $user = array();
        }
        if (!$user) {
            $user['uid'] = 0;
            $user['groupid'] = 6;
        }
        

        $_ENV['user']->refresh_session_time($sid, $user['uid']);
        $user['sid'] = $sid;
        $user['ip'] = $this->ip;
        $user['uid'] && $user['loginuser'] = $user['username'];
         $user['avatar'] = get_avatar_dir($user['uid']);
        $this->user = array_merge($user, $this->usergroup[$user['groupid']]);
       
       
    }

    /* 更新用户积分 */

    function credit($uid, $credit1, $credit2 = 0, $credit3 = 0, $operation = '') {
        if (!$operation)
            $operation = $this->get[0] . '/' . $this->get[1];
        //用户登陆只添加一次
        if ($operation == 'api_user/loginapi' && $this->db->result_first("SELECT uid FROM " . DB_TABLEPRE . "credit WHERE uid=$uid AND operation='api_user/loginapi' AND time>= " . strtotime(date("Y-m-d")))) {
            return false;
        }
      
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "credit(uid,time,operation,credit1,credit2) VALUES ($uid,{$this->time},'$operation',$credit1,$credit2) ");
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET credit2=credit2+$credit2,credit1=credit1+$credit1,credit3=credit3+$credit3 WHERE uid=$uid ");
         // runlog('checklogincredit', "SELECT uid FROM " . DB_TABLEPRE . "credit WHERE uid=$uid AND operation='user/login' AND time>= " . strtotime(date("Y-m-d")));
          //runlog('credit', "INSERT INTO " . DB_TABLEPRE . "credit(uid,time,operation,credit1,credit2) VALUES ($uid,{$this->time},'$operation',$credit1,$credit2) ");
         // runlog('updatecredit', "UPDATE " . DB_TABLEPRE . "user SET credit2=credit2+$credit2,credit1=credit1+$credit1,credit3=credit3+$credit3 WHERE uid=$uid ");
          if (2 == $this->user['grouptype']) {
            $currentcredit1 = $this->user['credit1'] + $credit1;
            $usergroup = $this->db->fetch_first("SELECT g.groupid FROM " . DB_TABLEPRE . "usergroup g WHERE  g.`grouptype`=2  AND $currentcredit1 >= g.creditslower ORDER BY g.creditslower DESC LIMIT 0,1");
            //判断是否需要升级
            if (is_array($usergroup) && ($this->user['groupid'] != $usergroup['groupid'])) {
                $groupid = $usergroup['groupid'];
                $this->db->query("UPDATE " . DB_TABLEPRE . "user SET groupid=$groupid WHERE uid=$uid ");
            }
        }
    }

    /* 权限检测 */

    function checkable($url,$querystring='') {
    	try{
    		$this->addsitelog($querystring);
    	}catch(Exception $e) 
    	{
    		
    	}
       
        $this->regular = $url;
        if (1 == $this->user['groupid'])
            return true;
            
          //  $pccaiji="";
         
        $regulars = explode(',', 'user/checkemail,chat/default,api_article/newqlist,api_article/list,api_user/editpwdapi,api_user/loginoutapi,api_user/bindloginapi,api_user/loginapi,api_user/bindregisterapi,api_user/registerapi,index/taobao,question/searchkey,pccaiji_catgory/addtopic,pccaiji_catgory/selectlist,pccaiji_catgory/list,topic/search,buy/buydetail,buy/default,download/default,tags/default,new/maketag,tag/default,user/regtip,new/default,user/deletexinzhi,user/editxinzhi,user/addxinzhi,topic/userxinzhi,topic/getone,topic/catlist,topic/hotlist,user/login,user/logout,user/code,user/getpass,user/resetpass,index/help,js/view,attach/upload,' . $this->user['regulars']);
        
      //  $regulars=array_merge($regulars,$this->regular);
      
        return in_array($url, $regulars);
    }

    /* IP禁止 */

    function banned() {
        $ips = $this->cache->load('banned');
        $ips = (bool) $ips ? $ips : array();
        $userip = explode(".", $this->ip);
        foreach ($ips as $ip) {
            $bannedtime = $ip['expiration'] + $ip['time'] - $this->time;
            if ($bannedtime > 0 && ($ip['ip1'] == '*' || $ip['ip1'] == $userip[0]) && ($ip['ip2'] == '*' || $ip['ip2'] == $userip[1]) && ($ip['ip3'] == '*' || $ip['ip3'] == $userip[2]) && ($ip['ip4'] == '*' || $ip['ip4'] == $userip[3])
            ) {
                exit('IP被禁止访问,如有问题请联系:' . $this->setting['admin_email']);
            }
        }
    }

    /* 	中转提示页面
      $ishtml=1 表示是跳转到静态网页
     */

    function message($message, $url = '') {
        $seotitle = '操作提示';
        if ('' == $url) {
            $redirect =isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:SITE_URL;
        } else if ('BACK' == $url || 'STOP' == $url||strstr($url, 'http:')) {
            $redirect = $url;
        } else {
        	
        
            $redirect = SITE_URL . $this->setting['seo_prefix'] . $url . $this->setting['seo_suffix'];
        }
        $tpldir = (0 === strpos($this->get[0], 'admin')) ? 'admin' : $this->setting['tpl_dir'];
       
       
//        if(strstr($_SERVER['HTTP_REFERER'],'topic/')||strstr($_SERVER['HTTP_REFERER'],'ut-')||strstr($_SERVER['HTTP_REFERER'],'article-')||strstr($_SERVER['HTTP_REFERER'],'xinzhi')){
//        	 include template('wztip', $tpldir);
//        }else{
//        	
//        }
    $panneltype='hidefixed';
      $hidefooter='hidefooter';
         include template('tip', $tpldir);
       
        exit;
    }

    /* 发送通知
      一、操作导致状态改变
      A、当问题被人回答，系统会自动给问题提出者发送通知
      B、当问题被采纳为答案，回答者会收到消息

      二、时间导致状态改变
      A、问题变为关闭状态，给提问者发通知

      三、$type说明:
      0:问题有新回答
      1:回答被采纳
      2:问题超时自动关闭
      3:回答有新评分
     */

    function send($uid, $qid, $type, $aid = 0) {
        $question = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "question WHERE id='$qid'");
        $msgtpl = unserialize($this->setting['msgtpl']);
        //消息模板
        $message = array();
        foreach ($msgtpl[$type] as $msg => $val) {
            $message[$msg] = str_replace('{wtbt}', $question['title'], $val);
            $message[$msg] = str_replace('{wtms}', $question['description'], $message[$msg]);
            $message[$msg] = str_replace('{wzmc}', $this->setting['site_name'], $message[$msg]);
            if ($aid) {
                $answer = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "answer WHERE id=$aid");
                $message[$msg] = str_replace('{hdnr}', $answer['content'], $message[$msg]);
            }
        }

        $message['content'] .='<br /> <a href="' . url('question/view/' . $qid, 1) . '">点击查看问题</a>';
        $time = $this->time;
        $msgfrom = $this->setting['site_name'] . '管理员';
        $touser = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid=" . $uid);
        //1,3,5,7 允许站内消息
        if ((1 & $touser['isnotify']) && $this->setting['notify_message']) {
            $this->db->query('INSERT INTO ' . DB_TABLEPRE . "message  SET `from`='" . $msgfrom . "' , `fromuid`=0 , `touid`=$uid  , `subject`='" . $message['title'] . "' , `time`=" . $time . " , `content`='" . $message['content'] . "'");
        }
        //2,3,6,7 允许发邮件
        if ((2 & $touser['isnotify']) && $this->setting['notify_mail']) {
            sendmail($touser, $message['title'], $message['content']);
        }
        //4,5,6,7 允许发手机短信
    }

    /* 检查验证码 */

    function checkcode() {
        $this->load('user');
       if (!isset($this->post['code'])&&isset($this->post['code']) && (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code())) {
            $this->message($this->post['state']."验证码错误!", 'BACK');
        }
         // if($this->setting['code_ask']&& (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code())){
          //	 $this->message("验证码错误!", 'BACK');
         // }
    }
    /*增加日志记录*/
    function addsitelog($guize,$miaoshu=''){
    	$uid=$this->user['uid'];
    	$username=$this->user['username'];
    	$miaoshu='';
    	$time=$this->time;
    	$guizearray=explode(',', 'index/notfound,user/ajaxloadmessage,user/code,admin_setting/ajaxcaiji');
    	
    	if($uid>0){
    	     if(!in_array($guize, $guizearray))
    		$this->db->query('INSERT INTO ' . DB_TABLEPRE . "site_log  SET `uid`='" . $uid . "'   , `username`='" . $username . "' , `time`=" . $time . " , `guize`='" . $guize . "', `miaoshu`='" . $miaoshu . "'");
    	}
      
    }

}

?>
