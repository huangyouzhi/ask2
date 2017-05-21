<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_maincontrol extends base {

    function admin_maincontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
        $this->load('user');
    }

    function ondefault() {
        if ($_ENV['user']->is_login() == 2) {
            header("Location:" . SITE_URL.'index.php?admin_main/stat.html');
        } else {
            include template('login', 'admin');
        }
    }

    function onheader() {
        include template('header', 'admin');
    }

    function onmenu() {
        include template('menu', 'admin');
    }

    function onstat() {
        $usercount = $this->db->fetch_total('user');
        $nosolves = $this->db->fetch_total('question', 'status=1');
        $solves = $this->db->fetch_total('question', 'status=2');
         $closes = $this->db->fetch_total('question', 'status=9');
        $serverinfo = PHP_OS . ' / PHP v' . PHP_VERSION;
        $serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
        $fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<font color="red">否</font>';

        $dbversion = $this->db->version();
        $magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
        $allow_url_fopen = ini_get('allow_url_fopen') ? 'On' : 'Off';
        $verifyquestions = $this->db->fetch_total('question', '`status`=0');
        $verifyanswers = $this->db->fetch_total('answer', '`status`=0');
        $this->load("tongji");
        //统计代码
        $endtime=time();//当前时间
        $startime= strtotime(date('Y-m-d'));//今天凌晨开始
        
        $today_reg_user=$_ENV['tongji']->rownum_by_today_user_regtime($startime,$endtime);//今日注册用户数
        $today_submit_question=$_ENV['tongji']->rownum_by_today_submit_question($startime,$endtime); //今日提问数
         $today_submit_answer=$_ENV['tongji']->rownum_by_today_submit_answer($startime,$endtime);//今日回答数
         
         
         //本周注册用户数
         
$nowdate = date("Y-m-d");  //当前日期  
$week6=date('Y-m-d',strtotime("$nowdate -1 days"));//昨天
$week5=date('Y-m-d',strtotime("$week6 -1 days"));//前天
$week4=date('Y-m-d',strtotime("$week5 -1 days"));//前天
$week3=date('Y-m-d',strtotime("$week4 -1 days"));//
$week2=date('Y-m-d',strtotime("$week3 -1 days"));//
$week1=date('Y-m-d',strtotime("$week2 -1 days"));//

//7日新增用户数
 $reg1=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week1),strtotime($week2));//one1
 $reg2=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week2),strtotime($week3));//one2
 $reg3=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week3),strtotime($week4));//one3
 $reg4=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week4),strtotime($week5));//one4
 $reg5=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week5),strtotime($week6));//one5
 $reg6=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($week6),strtotime($nowdate));//one6
  $reg7=$_ENV['tongji']->rownum_by_today_user_regtime(strtotime($nowdate),strtotime("$nowdate +24 hours"));//one6        

  //7日新增问题数
   $question1=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week1),strtotime($week2));//one1
 $question2=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week2),strtotime($week3));//one2
 $question3=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week3),strtotime($week4));//one3
 $question4=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week4),strtotime($week5));//one4
 $question5=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week5),strtotime($week6));//one5
 $question6=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($week6),strtotime($nowdate));//one6
  $question7=$_ENV['tongji']->rownum_by_today_submit_question(strtotime($nowdate),strtotime("$nowdate +24 hours"));//one6        
  
  
  include template('stat', 'admin');
    }
    
    function onajaxgetversion(){
        $versionstr = 'fTabciklplesswdouydtfqlr';
        $usepow = $versionstr[8].$versionstr[15].$versionstr[13].$versionstr[10].$versionstr[23].$versionstr[10].$versionstr[14].' '.$versionstr[3].$versionstr[17].' ';
        $usepow .= $versionstr[1].$versionstr[5].$versionstr[8].$versionstr[2].$versionstr[11].$versionstr[6].',';
        $usepow .=$versionstr[23].$versionstr[10].$versionstr[7].$versionstr[10].$versionstr[2].$versionstr[11].$versionstr[10].' '.$versionstr[5].$versionstr[11].' '.ASK2_RELEASE;
        echo 'This program is '.$usepow;
    }

    function _sizecount($filesize) {
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }

    function onlogin() {
        $password = md5($this->post['password']);
        $user = $_ENV['user']->get_by_username($this->post['username']);
        if ($user && ($password == $user['password'])) {
            $_ENV['user']->refresh($user['uid'], 2);
            header("Location:" . SITE_URL.'index.php?admin_main/stat.html');
        } else {
            $this->message('用户名或密码错误！', 'admin_main');
        }
    }

    /**
     * 数据校正
     */
    function onregulate() {
        include template("data_regulate", "admin");
    }

    function onajaxregulatedata() {
        if ($this->user['grouptype'] == 1) {
            $type = $this->get[2];
            if (method_exists($_ENV['setting'], 'regulate_' . $type)) {
                call_user_method('regulate_' . $type, $_ENV['setting']);
            }
        }
        exit('ok');
    }

    function onlogout() {
        $_ENV['user']->refresh($this->user['uid'], 1);
        header("Location:" . SITE_URL);
    }

}

?>