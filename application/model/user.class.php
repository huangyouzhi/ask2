<?php

!defined('IN_ASK2') && exit('Access Denied');

class usermodel {

    var $db;
    var $base;
  var $search;
    var $index;
    function usermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        if ($this->base->setting['xunsearch_open']) {
            require_once $this->base->setting['xunsearch_sdk_file'];
           
            $xs = new XS('question');
           
            $this->search = $xs->search;
            
             
            $this->index = $xs->index;
        
             
        }
    }

    function get_by_uid($uid, $loginstatus = 1) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
        $user['avatar'] = get_avatar_dir($uid);
        $user['register_time'] = tdate($user['regtime']);
        $user['lastlogin'] = tdate($user['lastlogin']);
        $user['grouptitle'] = $this->base->usergroup[$user['groupid']]['grouptitle'];
        $user['category'] = $this->get_category($user['uid']);
        ($loginstatus == 1) && $user['islogin'] = $this->is_login($uid);
        ($loginstatus == 2) && $user['refresh_time'] = tdate($this->get_refresh_time($uid));
        return $user;
    }

    function get_by_username($username) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE username='$username' or email='$username' or phone='$username'");
        return $user;
    }
  function get_by_openid($openid) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE openid='$openid'");
        return $user;
    }

    function get_by_email($email) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE email='$email'");
        return $user;
    }
  function get_by_phone($phone) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE phone='$phone'");
        return $user;
    }
    //找回密码
    function get_by_name_email($name, $email) {
        $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE email='$email' AND `username`='$name'");
        return $user;
    }

    /* 采纳率 */

    function adoptpercent($user) {
        $adoptpercent = 0;
        if (0 != $user['answers']) {
            $adoptpercent = round(($user['adopts'] / $user['answers']), 3) * 100;
        }
        return $adoptpercent;
    }

    function get_list($start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user  ORDER BY uid DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['lastlogintime'] = tdate($user['lastlogin']);
            $user['regtime'] = tdate($user['regtime']);
            $userlist[] = $user;
        }
        return $userlist;
    }
 
    function get_active_list($start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user ORDER BY answers DESC,articles DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $user['signature']=$user['signature']==null? '用户很懒，什么都没留下':$user['signature'];
             $user['signature']=cutstr( checkwordsglobal(strip_tags($user['signature'])), 40,'...');
               $is_followed = $this->is_followed( $user['uid'], $this->base->user['uid']);
                $user['hasfollower']=$is_followed==0 ? "0":"1";
                 $user['category'] = $this->get_category($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }
    //获取采集用户列表
 function get_caiji_list($start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE fromsite=1 ORDER BY lastlogin DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }
   function get_active_list_bynosign($start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user where signature='这个人很懒，什么都没留下' or signature is null ORDER BY lastlogin DESC,credit2 DESC  LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }
    function get_lastest_register($start = 0, $limit = 5) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user ORDER BY regtime DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
        	  $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_answer_top($start = 0, $limit = 8) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user ORDER BY answers DESC,lastlogin DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
        	  $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

   
    function list_by_search_condition($condition, $start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query('SELECT * FROM ' . DB_TABLEPRE . "user WHERE $condition ORDER BY `uid` DESC LIMIT $start , $limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['regtime'] = tdate($user['regtime']);
              $user['avatar'] = get_avatar_dir($user['uid']);
            $user['lastlogintime'] = tdate($user['lastlogin']);
           
        $user['category'] = $this->get_category($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    /* 根据用户的一段时间的积分排序，只取前100名。 */

    function list_by_credit($type = 0, $limit = 100) {
        $userlist = array();
        $starttime = 0;
        if (1 == $type) {
            $starttime = $this->base->time - 7 * 24 * 3600;
        }
        if (2 == $type) {
            $starttime = $this->base->time - 30 * 24 * 3600;
        }
        $sqlarray = array(
            'SELECT u.uid,u.groupid, u.username,u.gender,u.lastlogin,u.credit2,u.questions,u.answers,u.adopts FROM ' . DB_TABLEPRE . "user  u ORDER BY `credit2` DESC,u.answers DESC  LIMIT 0,$limit",
            "SELECT u.uid,u.groupid, u.username,u.gender,u.lastlogin,sum( c.credit2 ) credit2,u.questions,u.answers,u.adopts FROM " . DB_TABLEPRE . "user u," . DB_TABLEPRE . "credit c   WHERE u.uid=c.uid AND c.time>$starttime   GROUP BY u.uid ORDER BY credit2  DESC,u.answers DESC LIMIT 0,$limit",
            "SELECT u.uid,u.groupid, u.username,u.gender,u.lastlogin,sum( c.credit2 ) credit2,u.questions,u.answers,u.adopts  FROM " . DB_TABLEPRE . "user u," . DB_TABLEPRE . "credit c   WHERE u.uid=c.uid AND c.time>$starttime   GROUP BY u.uid ORDER BY credit2  DESC,u.answers DESC LIMIT 0,$limit"
        );
        $query = $this->db->query($sqlarray[$type]);
        while ($user = $this->db->fetch_array($query)) {
            $user['gender'] = (1 == $user['gender']) ? '男' : '女';
            $user['lastlogin'] = tdate($user['lastlogin']);
            $user['grouptitle'] = $this->base->usergroup[$user['groupid']]['grouptitle'];
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function refresh($uid, $islogin = 1, $cookietime = 0) {
        @$sid = tcookie('sid');
        $this->base->user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user u," . DB_TABLEPRE . "usergroup g WHERE u.uid=$uid AND u.groupid=g.groupid");
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `lastlogin`={$this->base->time}  WHERE `uid`=$uid"); //更新最后登录时间
       $this->db->query("REPLACE INTO " . DB_TABLEPRE . "session (sid,uid,islogin,ip,`time`) VALUES ('$sid',$uid,$islogin,'{$this->base->ip}',{$this->base->time})");
        $password = $this->base->user['password'];
        $auth = authcode("$uid\t$password", 'ENCODE');
        if ($cookietime)
            tcookie('auth', $auth, $cookietime);
        else
            tcookie('auth', $auth);

        tcookie('loginuser', '');
        $this->base->user['newmsg'] = 0;
    }

    function refresh_session_time($sid, $uid) {
        $lastrefresh = intval(tcookie("lastrefresh"));
        if (!$lastrefresh) {
            if ($uid) {
                $this->db->query("UPDATE " . DB_TABLEPRE . "session SET `time` = {$this->base->time} WHERE sid='$sid'");
            } else {
               $session = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "session WHERE sid='$sid'");
                if ($session) {
                   $this->db->query("UPDATE " . DB_TABLEPRE . "session SET `time` = {$this->base->time} WHERE sid='$sid'");
                } else {
                   $this->db->query("INSERT INTO " . DB_TABLEPRE . "session (sid,`ip`,`time`) VALUES ('$sid','{$this->base->ip}',{$this->base->time})");
                }
            }
            tcookie("lastrefresh", '1', 60);
        }
    }

    /* 添加用户，本函数需要返回uid */

    function add($username, $password, $email = '', $uid = 0) {
        $password = md5($password);
        if ($uid) {
            $this->db->query("REPLACE INTO  " . DB_TABLEPRE . "user (uid,username,password,email,regip,regtime,`lastlogin`) VALUES ('$uid','$username','$password','$email','" . getip() . "',{$this->base->time},{$this->base->time})");
        } else {
            $this->db->query("INSERT INTO " . DB_TABLEPRE . "user(username,password,email,regip,regtime,`lastlogin`) values ('$username','$password','$email','" . getip() . "',{$this->base->time},{$this->base->time})");
            $uid = $this->db->insert_id();
        }
        return $uid;
    }
    function caijiadd($username, $password, $email = '',$fromsite=1) {
        $password = md5($password);
     
            $this->db->query("INSERT INTO " . DB_TABLEPRE . "user(username,password,email,fromsite,regip,regtime,`lastlogin`) values ('$username','$password','$email','$fromsite','" . getip() . "',{$this->base->time},{$this->base->time})");
            $uid = $this->db->insert_id();
        
        return $uid;
    }
    /* 微信授权注册 */

    function weixinadd($username, $password, $openid = '') {
        $password = md5($password);
       
            $this->db->query("INSERT INTO " . DB_TABLEPRE . "user(username,password,openid,regip,regtime,`lastlogin`) values ('$username','$password','$openid','" . getip() . "',{$this->base->time},{$this->base->time})");
            $uid = $this->db->insert_id();
        
        return $uid;
    }
  function adduserapi($username, $password, $email = '',$groupid=7, $uid = 0, $phone = 0) {
        $password = md5($password);
        if ($uid) {
            $this->db->query("REPLACE INTO  " . DB_TABLEPRE . "user (uid,username,password,email,regip,regtime,`lastlogin`,groupid) VALUES ('$uid','$username','$password','$email','" . getip() . "',{$this->base->time},{$this->base->time},'$groupid')");
        } else {
            $this->db->query("INSERT INTO " . DB_TABLEPRE . "user(username,password,email,regip,regtime,`lastlogin`,groupid,phone) values ('$username','$password','$email','" . getip() . "',{$this->base->time},{$this->base->time},'$groupid','$phone')");
            $uid = $this->db->insert_id();
        }
        return $uid;
    }

    //ip地址限制
    function is_allowed_register() {
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $usernum = $this->db->result_first("SELECT count(*) FROM " . DB_TABLEPRE . "user WHERE regip='{$this->base->ip}' AND regtime>$starttime AND regtime<$endtime ");
        if ($usernum >= $this->base->setting['max_register_num']) {
            return false;
        }
        return true;
    }

    /* 修改用户密码 */

    function uppass($uid, $password) {
        $password = md5($password);
      
        $this->db->query('UPDATE ' . DB_TABLEPRE . "user SET `password`='" . $password . "' WHERE `uid`=$uid ");
    }

    /* 更新用户信息 */

    function update($uid, $gender, $bday, $phone, $qq, $msn, $introduction, $signature, $isnotify = 1) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `gender`='$gender',`bday`='$bday',`phone`='$phone',`qq`='$qq',`msn`='$msn',`introduction`='$introduction',`signature`='$signature',`isnotify`='$isnotify'  WHERE `uid`=$uid");
    }

    function update_email($email, $uid) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `email`='$email' WHERE `uid`=$uid");
    }
    /*修改邮箱并修改激活为0 */
    function update_emailandactive($email,$activecode,$uid) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `activecode`='$activecode', `active`=0,`email`='$email' WHERE `uid`=$uid");
    }
     /*修改激活为1,邮箱激活 */
    function update_useractive($uid){
    	$this->db->query("UPDATE " . DB_TABLEPRE . "user SET  `active`=1 WHERE `uid`=$uid");
    }
    /* 礼品兑换用户信息 */

    function update_gift($uid, $realname, $email, $phone, $qq) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `realname`='$realname',`email`='$email',`phone`='$phone',`qq`='$qq' WHERE `uid`=$uid");
    }

    /* 后台更新用户信息 */

    function update_user($uid, $username, $passwd, $email, $groupid, $credits, $credit1, $credit2, $gender, $bday, $phone, $qq, $msn, $introduction, $signature,$isblack=0) {
     
    	$this->db->query("UPDATE " . DB_TABLEPRE . "user SET `username`='$username',`password`='$passwd',`isblack`='$isblack',`email`='$email',`groupid`='$groupid',`credits`=$credits,`credit1`=$credit1,`credit2`=$credit2,`gender`='$gender',`bday`='$bday',`phone`='$phone',`qq`='$qq',`msn`='$msn',introduction='$introduction',`signature`='$signature'   WHERE `uid`=$uid");
    }

    /* 更新authstr */

    function update_authstr($uid, $authstr) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `authstr`='$authstr'  WHERE `uid`=$uid");
    }

    /* 更新username */

    function update_username($uid, $username,$useremail) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `username`='$username' ,email='$useremail'  WHERE `uid`=$uid");
    }

    /* 删除用户 */

    function remove($uids, $all = 0) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "user` WHERE `uid` IN ($uids)");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "famous` WHERE `uid` IN ($uids)");
        /* 删除问题和回答 */
        if ($all) {
            $this->db->query("DELETE FROM `" . DB_TABLEPRE . "question` WHERE `authorid` IN ($uids)");
            $this->db->query("DELETE FROM `" . DB_TABLEPRE . "answer` WHERE `authorid` IN ($uids)");
            $this->db->query("UPDATE `" . DB_TABLEPRE . "question` SET answers=answers-1 WHERE `authorid` IN ($uids)");
        }
    }

    function logout() {
        tcookie('sid', '', 0);
        tcookie('auth', '', 0);
        tcookie('loginuser', '', 0);
        $lasttime = $this->db->result_first("SELECT MAX(time) FROM ".DB_TABLEPRE."session WHERE uid=" . $this->base->user['uid']);
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "session WHERE uid=" . $this->base->user['uid']." AND `time`<$lasttime");
    }

    function save_code($code) {
        $uid = $this->base->user['uid'];
        $sid = $this->base->user['sid'];
        $islogin = $this->db->result_first("SELECT islogin FROM " . DB_TABLEPRE . "session WHERE sid='$sid'");
        $islogin = $islogin ? $islogin : 0;
        $this->db->query("REPLACE INTO " . DB_TABLEPRE . "session (sid,uid,code,islogin,`time`) VALUES ('$sid',$uid,'$code','$islogin',{$this->base->time})");
    }

    function get_code() {
        $sid = $this->base->user['sid'];
        return $this->db->result_first("SELECT code FROM " . DB_TABLEPRE . "session WHERE sid='$sid'");
    }

    function is_login($uid = 0) {
        (!$uid) && $uid = $this->base->user['uid'];
        $onlinetime = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        $islogin = $this->db->result_first("SELECT islogin FROM " . DB_TABLEPRE . "session WHERE uid=$uid AND time>$onlinetime");
        if ($islogin && $uid > 0) {
            return $islogin;
        }
        return false;
    }

    function get_refresh_time($uid) {
        return $this->db->result_first("SELECT time FROM " . DB_TABLEPRE . "session WHERE uid=$uid ORDER BY time DESC");
    }

    /* 客服端通行证 */

    function passport_client() {
        $passport_action = 'passport_' . $this->base->get[1]; //login、logout、register
        $location = $this->base->setting['passport_server'] . '/' . $this->base->setting[$passport_action];
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        header('location:' . $location . (false === strpos($location, '?') ? '?' : '&') . 'forward=' . $forward);
        exit;
    }

    /* 服务端通行证 */

    function passport_server($forward) {
        $action = $this->base->get[1];
        ('register' == $action) && $action = 'login';
        $member['username'] = $this->base->user['username'];
        $member['password'] = $this->base->user['password'];
        $member['email'] = $this->base->user['email'];
        $member['cktime'] = $this->base->time + (60 * 60 * 24 * 365);
        $userstr = 'time=' . $this->base->time;
        foreach ($member as $key => $val) {
            $userstr .= "&$key=$val";
        }
        $userdb = authcode($userstr, 'ENCODE', $this->base->setting['passport_key']);
        $verify = md5($action . $userdb . $forward . $this->base->setting['passport_key']);
        $location = $this->base->setting['passport_client'] . '?action=' . $action . '&userdb=' . urlencode($userdb) . '&forward=' . urlencode($forward) . '&verify=' . $verify;
        header('location:' . $location);
        exit;
    }

    /* 用户积分明细 */

    function credit_detail($uid) {
        $detail1 = $detail2 = $detail3 = array('reward' => 0, 'punish' => 0, 'offer' => 0, 'adopt' => 0, 'other' => 0);
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "credit c  where c.uid=" . $uid);
        while ($credit = $this->db->fetch_array($query)) {
            switch ($credit['operation']) {
                case 'reward'://奖励得分
                    $detail1['reward']+=$credit['credit1'];
                    $detail2['reward']+=$credit['credit2'];
                    $detail3['reward']+=$credit['credit3'];
                    break;
                case 'punish'://处罚得分
                    $detail1['punish']+=$credit['credit1'];
                    $detail2['punish']+=$credit['credit2'];
                    $detail3['punish']+=$credit['credit3'];
                    break;
                case 'offer'://悬赏付出
                    $detail2['offer']+=$credit['credit2'];
                    break;
                case 'adopt'://回答的问题被采纳为答案
                    $detail2['adopt']+=$credit['credit2'];
                    break;
                default:
                    $detail1['other']+=$credit['credit1'];
                    $detail2['other']+=$credit['credit2'];
                    $detail3['other']+=$credit['credit3'];
                    break;
            }
        }
        return array($detail1, $detail2);
    }

    /* 检测用户名合法性 */

    function check_usernamecensor($username) {
        $censorusername = $this->base->setting['censor_username'];
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censorusername = trim($censorusername)), '/')) . ')$/i';
        if ($censorusername && preg_match($censorexp, $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /* 检测邮件地址合法性 */

    function check_emailaccess($email) {
        $setting = $this->base->setting;
        $accessemail = $setting['access_email'];
        $censoremail = $setting['censor_email'];
        $accessexp = '/(' . str_replace("\r\n", '|', preg_quote(trim($accessemail), '/')) . ')$/i';
        $censorexp = '/(' . str_replace("\r\n", '|', preg_quote(trim($censoremail), '/')) . ')$/i';
        if ($accessemail || $censoremail) {
            if (($accessemail && !preg_match($accessexp, $email)) || ($censoremail && preg_match($censorexp, $email))) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    function add_category($cid, $uid) {
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "user_category(cid,uid) VALUES ($cid,$uid)");
    }

    function get_category($uid) {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user_category WHERE uid=$uid");
        $categorylist = array();
        while ($category = $this->db->fetch_array($query)) {
            $category['categoryname'] = $this->base->category[$category['cid']]['name'];
            $categorylist[] = $category;
        }
        return $categorylist;
    }

    function remove_category($cid, $uid) {
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "user_category WHERE cid=$cid AND uid=$uid");
    }

    function update_elect($uid, $elect) {
        $elect && $elect = $this->base->time;
        $this->db->query("UPDATE `" . DB_TABLEPRE . "user` SET `elect`=$elect WHERE `uid`=$uid");
    }

    function update_expert($uids, $type) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET expert=$type WHERE uid IN (" . implode(",", $uids) . ")");
    }
  function update_caijiuser($uids, $type) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET fromsite=$type WHERE uid IN (" . implode(",", $uids) . ")");
    }
    
    function get_login_auth($uid, $type = 'qq') {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "login_auth WHERE type='$type' AND uid=$uid");
    }

    function remove_login_auth($uid, $type = 'qq') {
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "login_auth WHERE type='$type' AND uid=$uid");
    }

    /* 获取所有注册用户数目 */

    function rownum_alluser() {
        return array($this->db->fetch_total('user', ' 1=1'));
    }

    /* 获取所有在线用户数目 */

    function rownum_onlineuser() {
        $end = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        $query = $this->db->query("SELECT *  FROM " . DB_TABLEPRE . "session WHERE time>$end GROUP BY ip");
        $ret = $this->db->num_rows($query);
        return $ret;
    }

    function list_online_user($start = 0, $limit = 50) {
        $onlinelist = array();
        $end = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        $query = $this->db->query("SELECT s.ip,s.uid,u.username,s.time FROM " . DB_TABLEPRE . "session AS s LEFT  JOIN " . DB_TABLEPRE . "user AS u ON u.uid=s.uid WHERE s.time>$end GROUP BY s.ip ORDER BY s.time DESC LIMIT $start,$limit");
        while ($online = $this->db->fetch_array($query)) {
            $online['online_time'] = tdate($online['time']);
            $onlinelist[] = $online;
        }
        return $onlinelist;
    }

    /* 关注者列表 */

    function get_follower($uid, $start = 0, $limit = 20) {
        $followerlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user_attention WHERE uid=$uid ORDER BY time DESC LIMIT $start,$limit");
        while ($follower = $this->db->fetch_array($query)) {
            $follower['avatar'] = get_avatar_dir($follower['followerid']);
             $is_followed = $this->is_followed( $follower['followerid'], $this->base->user['uid']);
                $follower['hasfollower']=$is_followed==0 ? "0":"1";
                $user=$this->get_by_uid($follower['followerid']);
                $follower['info']= $user;
            $followerlist[] = $follower;
        }
        return $followerlist;
    }

    /* 已关注列表 */

    function get_attention($followerid, $start = 0, $limit = 20) {
        $attentionlist = array();
        $query = $this->db->query("SELECT u.uid,u.username,u.gender FROM " . DB_TABLEPRE . "user_attention AS ua," . DB_TABLEPRE . "user AS u WHERE ua.uid=u.uid AND ua.followerid=$followerid ORDER BY ua.time DESC LIMIT $start,$limit");
        while ($attention = $this->db->fetch_array($query)) {
            $attention['avatar'] = get_avatar_dir($attention['uid']);
               $is_followed = $this->is_followed( $attention['uid'], $this->base->user['uid']);
              $attention['hasfollower']=$is_followed==0 ? "0":"1";
                  $user=$this->get_by_uid($attention['uid']);
                $attention['info']= $user;
            $attentionlist[] = $attention;
        }
        return $attentionlist;
    }

    /* 已关注列表 */

    function get_attention_question($followerid, $start = 0, $limit = 20) {
        $questionlist = array();
        $query = $this->db->query("SELECT q.*  FROM " . DB_TABLEPRE . "question AS q," . DB_TABLEPRE . "question_attention as qa WHERE q.id=qa.qid AND qa.followerid=$followerid ORDER BY qa.time DESC LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
        	$question['avatar'] = get_avatar_dir($question['authorid']);
        	 $question['image']=getfirstimg($question['description']);
              $question['description']=cutstr( checkwordsglobal(strip_tags($question['description'])), 240,'...');
            $question['attention_time'] = tdate($question['time']);
            $question['category_name'] = $this->base->category[$question['cid']]['name'];
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    function rownum_attention_question($followerid) {
        return $this->db->result_first("SELECT count(*)  FROM " . DB_TABLEPRE . "question AS q," . DB_TABLEPRE . "question_attention as qa WHERE q.id=qa.qid AND qa.followerid=$followerid");
    }
  /* 已关注分类话题列表 */

    function get_attention_category($followerid, $start = 0, $limit = 20) {
        $modellist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "categotry_follower WHERE uid=$followerid ORDER BY `time` DESC LIMIT $start,$limit");
        while ($model = $this->db->fetch_array($query)) {
        	$c_time=tdate($model['time']);
        	$c_uid=$model['uid'];
        	$c_cid=$model['cid'];
        $model=$this->get_cat_bycid($model['cid']);
          $model['uid']=$c_uid;
           $model['cid']=$c_cid;
         $model['avatar'] = get_avatar_dir($c_uid);
          $model['doing_time'] = $c_time;
            	$model['url']= urlmap('category/view/' . $model['cid'], 2);
            	  $model['url'] = url($model['url']);
            $modellist[] = $model;
        }
        return $modellist;
    }
   function rownum_attention_category($followerid) {
        return $this->db->result_first("SELECT count(*)  FROM " . DB_TABLEPRE . "categotry_follower WHERE uid=$followerid ");
    }
    function get_cat_bycid($id) {
        $category= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "category WHERE id='$id'");
    $category['image']=get_cid_dir($category['id'],'small');
   $category['follow'] = $this->is_followedcid($category['id'], $this->base->user['uid']);
    $category['miaosu']=cutstr( checkwordsglobal(strip_tags( $category['miaosu'])), 140,'...');
        	 	$category['bigimage']=get_cid_dir($category['id'],'big');
        	 	return $category;
    }
   /* 是否关注分类 */

    function is_followedcid($cid, $uid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "categotry_follower WHERE uid=$uid AND cid=$cid");
    }
    
    /* 是否关注用户 */

    function is_followed($uid, $followerid) {
        return $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "user_attention WHERE uid=$uid AND followerid=$followerid");
    }

    /* 关注 */

    function follow($sourceid, $followerid, $follower, $type = 'question') {
        $sourcefield = 'qid';
        ($type != 'question') && $sourcefield = 'uid';
        $this->db->query("INSERT INTO " . DB_TABLEPRE . $type . "_attention($sourcefield,followerid,follower,time) VALUES ($sourceid,$followerid,'$follower',{$this->base->time})");
        if ($type == 'question') {
        	 $this->db->query('REPLACE INTO `' . DB_TABLEPRE . "favorite`(`qid`,`uid`,`time`) values ($sourceid,$followerid,{$this->base->time})");
            $this->db->query("UPDATE " . DB_TABLEPRE . "question SET attentions=attentions+1 WHERE `id`=$sourceid");
            
             if ($this->base->setting['xunsearch_open']) {
             $_question=$this->getquestionbyqid($sourceid);
            $question = array();
            $question['id'] = $sourceid;
            $question['attentions'] = $_question['attentions'];
             $question['price'] = $_question['price'];
            $doc = new XSDocument;
            $doc->setFields($question);
            $this->index->update($doc);
        }
        } else if ($type == 'user') {
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET followers=followers+1 WHERE `uid`=$sourceid");
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET attentions=attentions+1 WHERE `uid`=$followerid");
        }
    }

    /* 取消关注 */

    function unfollow($sourceid, $followerid, $type = 'question') {
        $sourcefield = 'qid';
        ($type != 'question') && $sourcefield = 'uid';
        $this->db->query("DELETE FROM " . DB_TABLEPRE . $type . "_attention WHERE $sourcefield=$sourceid AND followerid=$followerid");
        if ($type == 'question') {
            $this->db->query("UPDATE " . DB_TABLEPRE . "question SET attentions=attentions-1 WHERE `id`=$sourceid");
            
             if ($this->base->setting['xunsearch_open']) {
            $_question=$this->getquestionbyqid($sourceid);
            $question = array();
            $question['id'] = $sourceid;
            $question['attentions'] = $_question['attentions'];
             $question['shangjin'] = $_question['shangjin'];
              $question['price'] = $_question['price'];
            $doc = new XSDocument;
            $doc->setFields($question);
            $this->index->update($doc);
        }
        
        } else if ($type == 'user') {
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET followers=followers-1 WHERE `uid`=$sourceid");
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET attentions=attentions-1 WHERE `uid`=$followerid");
        }
    }
   function getquestionbyqid($id) {
        $question = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "question WHERE id='$id'");
  
        return $question;
    }

}

?>