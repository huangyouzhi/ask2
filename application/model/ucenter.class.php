<?php

!defined('IN_ASK2') && exit('Access Denied');

class ucentermodel {

    var $db;
    var $base;

    function ucentermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        @include ASK2_ROOT . '/data/ucconfig.inc.php';
        !defined('UC_API') && define('UC_API', '1');
        require_once ASK2_ROOT . '/uc_client/client.php';
    }

    /* 同步uc注册 */

    function login($username, $password) {
        $tuser = $_ENV['user']->get_by_username($username);
        $ucenter_user = uc_get_user($username);
        if (!$ucenter_user && ($tuser['username']==$username && $password==$tuser['password'])){
            $uid = uc_user_register($tuser['username'], $this->base->post['password'], $tuser['email']);
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET uid=$uid WHERE uid=".$tuser['uid']);
        }
        //通过接口判断登录帐号的正确性，返回值为数组
        list($uid, $username, $password, $email) = uc_user_login($username, $password);
        if ($uid > 0) {
            $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
            if (!$user) {
                $_ENV['user']->add($username, $password, $email, $uid);
            }
            if ($user['password'] != $password) {
                $this->db->query("UPDATE " . DB_TABLEPRE . "user SET password='$password' WHERE uid=$uid");
            }
            $_ENV['user']->refresh($uid);
            //生成同步登录的代码
            $ucsynlogin = uc_user_synlogin($uid);
            $this->base->message('登录成功!' . $ucsynlogin . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
        } elseif ($uid == -1) {
            $this->base->message('用户不存在,或者被删除!');
        } elseif ($uid == -2) {
            $this->base->message('密码错误!');
        } else {
            $this->base->message('未定义!');
        }
    }
    
/* 同步uc注册 */

    function ajaxlogin($username, $password) {
        $tuser = $_ENV['user']->get_by_username($username);
        $ucenter_user = uc_get_user($username);
        if (!$ucenter_user && ($tuser['username']==$username && $password==$tuser['password'])){
            $uid = uc_user_register($tuser['username'], $password, $tuser['email']);
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET uid=$uid WHERE uid=".$tuser['uid']);
        }
        //通过接口判断登录帐号的正确性，返回值为数组
        list($uid, $username, $password, $email) = uc_user_login($username, $password);
        if ($uid > 0) {
            $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
            if (!$user) {
                $_ENV['user']->add($username, $password, $email, $uid);
            }
            if ($user['password'] != $password) {
                $this->db->query("UPDATE " . DB_TABLEPRE . "user SET password='$password' WHERE uid=$uid");
            }
            $_ENV['user']->refresh($uid);
            //生成同步登录的代码
            $ucsynlogin = uc_user_synlogin($uid);
            return 'ok';
            //$this->base->message('登录成功!' . $ucsynlogin . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
        } elseif ($uid == -1) {
        	return '用户不存在,或者被删除!';
           // $this->base->message('用户不存在,或者被删除!');
        } elseif ($uid == -2) {
        	return '密码错误!';
           // $this->base->message('密码错误!');
        } else {
        	return '未定义!';
            //$this->base->message('未定义!');
        }
    }
    

    /* 同步uc注册 */

    function register() {
        $activeuser = uc_get_user($this->base->post['username']);
        if ($activeuser) {
            $this->base->message('该用户无需注册，请直接登录!', 'user/login');
        }
        $uid = uc_user_register($this->base->post['username'], $this->base->post['password'], $this->base->post['email']);
        if ($uid <= 0) {
            if ($uid == -1) {
                $this->base->message('用户名不合法');
            } elseif ($uid == -2) {
                $this->base->message('包含要允许注册的词语');
            } elseif ($uid == -3) {
                $this->base->message('用户名已经存在');
            } elseif ($uid == -4) {
                $this->base->message('Email 格式有误');
            } elseif ($uid == -5) {
                $this->base->message('Email 不允许注册');
            } elseif ($uid == -6) {
                $this->base->message('该 Email 已经被注册');
            } else {
                $this->base->message('未定义');
            }
        } else {
            $_ENV['user']->add($this->base->post['username'], $this->base->post['password'], $this->base->post['email'], $uid);
            $_ENV['user']->refresh($uid);
            $ucsynlogin = uc_user_synlogin($uid);
            $this->base->message('注册成功' . $ucsynlogin . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
        }
    }
 /* 同步uc注册 */

    function ajaxregister($username,$password,$email) {
        $activeuser = uc_get_user($this->base->post['username']);
        if ($activeuser) {
        	return '该用户无需注册，请直接登录!';
            //$this->base->message('该用户无需注册，请直接登录!', 'user/login');
        }
        $uid = uc_user_register($username,$password,$email);
        if ($uid <= 0) {
            if ($uid == -1) {
            	return '用户名不合法';
               // $this->base->message('用户名不合法');
            } elseif ($uid == -2) {
            	return '包含要允许注册的词语';
                //$this->base->message('包含要允许注册的词语');
            } elseif ($uid == -3) {
            	return '用户名已经存在';
              //  $this->base->message('用户名已经存在');
            } elseif ($uid == -4) {
            	return 'Email 格式有误';
               // $this->base->message('Email 格式有误');
            } elseif ($uid == -5) {
            	return 'Email 不允许注册';
                //$this->base->message('Email 不允许注册');
            } elseif ($uid == -6) {
            	return '该 Email 已经被注册';
                //$this->base->message('该 Email 已经被注册');
            } else {
            	return '未定义';
               // $this->base->message('未定义');
            }
        } else {
            $_ENV['user']->add($username,$password,$email, $uid);
            $_ENV['user']->refresh($uid);
            $ucsynlogin = uc_user_synlogin($uid);
            return 'ok';
           // $this->base->message('注册成功' . $ucsynlogin . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
        }
    }
    /* 同步uc退出系统 */

    function logout() {
        $_ENV['user']->logout();
        $ucsynlogout = uc_user_synlogout();
        $this->base->message('退出成功' . $ucsynlogout . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
    }
    /* 同步uc退出系统 */

    function ajaxlogout() {
        $_ENV['user']->logout();
        $ucsynlogout = uc_user_synlogout();
        return 'ok';
       // $this->base->message('退出成功' . $ucsynlogout . '<br><a href="' . $_SERVER['PHP_SELF'] . '">继续</a>');
    }

    /**
     * 兑换积分
     * @param  integer $uid 用户ID
     * @param  integer $fromcredits 原积分
     * @param  integer $tocredits 目标积分
     * @param  integer $toappid 目标应用ID
     * @param  integer $amount 积分数额
     * @return boolean
     */
    function exchange($uid, $fromcredits, $tocredits, $toappid, $amount) {
        $ucresult = uc_credit_exchange_request($uid, $fromcredits, $tocredits, $toappid, $amount);
        return $ucresult;
    }

    /* 提出问题feed */

    function ask_feed($qid, $title, $description) {
        global $setting;
        $feed = array();
        $feed['icon'] = 'thread';
        $feed['title_template'] = '<b>{author} 在 {app} 发出了问题求助</b>';
        $feed["title_data"] = array(
            "author" => '<a href="space.php?uid=' . $this->base->user['uid'] . '">' . $this->base->user['username'] . '</a>',
            "app" => '<a href="' . SITE_URL . '">' . $setting['site_name'] . '</a>'
        );
        $feed['body_template'] = '<b>{subject}</b><br>{message}';
        $feed["body_data"] = array(
            "subject" => '<a href="' . SITE_URL . $setting['seo_prefix'] . 'question/view/' . $qid . $setting['seo_suffix'] . '">' . $title . '</a>',
            "message" => $description
        );
        uc_feed_add($feed['icon'], $this->base->user['uid'], $this->base->user['username'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data']);
    }

    /* 回答问题feed */

    function answer_feed($question, $content) {
        global $setting;
        $feed = array();
        $feed['icon'] = 'post';
        $feed['title_template'] = '<b>{author} 在 {app} 回答了{asker} 的问题</b>';
        $feed["title_data"] = array(
            "author" => '<a href="space.php?uid=' . $this->base->user['uid'] . '">' . $this->base->user['username'] . '</a>',
            "asker" => '<a href="space.php?uid=' . $question['authorid'] . '">' . $question['author'] . '</a>',
            "app" => '<a href="' . SITE_URL . '">' . $setting['site_name'] . '</a>'
        );
        $feed['body_template'] = '<b>{subject}</b><br>{message}';
        $feed["body_data"] = array(
            "subject" => '<a href="' . SITE_URL . $setting['seo_prefix'] . 'question/view/' . $question['id'] . $setting['seo_suffix'] . '">' . $question['title'] . '</a>',
            "message" => $content
        );
        uc_feed_add($feed['icon'], $this->base->user['uid'], $this->base->user['username'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data']);
    }

    function set_avatar($uid) {
        return uc_avatar($uid);
    }

    function uppass($username, $oldpw, $newpw, $email,$ignoreoldpw = 0) {
        uc_user_edit($username, $oldpw, $newpw, $email,$ignoreoldpw);
    }

}

?>
