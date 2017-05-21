<?php

!defined('IN_ASK2') && exit('Access Denied');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require ASK2_ROOT . '/lib/db.class.php';
require ASK2_ROOT . '/lib/global.func.php';
require ASK2_ROOT . '/lib/cache.class.php';
require ASK2_APP_ROOT . '/model/base.class.php';
 

class sowenda {

    var $get = array();
    var $post = array();
    var $vars = array();
    var $_querystring='';
    function sowenda() {
    	
        $this->init_request();
        $this->load_control();
    }

   
    function init_request() {
        if (!file_exists(ASK2_ROOT . '/data/install.lock')) {
            header('location:install/index.php');
            exit();
        }
        require ASK2_ROOT . '/config.php';
        header('Content-type: text/html; charset=' . ASK2_CHARSET);
        $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
       
        $pos = strrpos($querystring, '.');
        if ($pos !== false) {
            $querystring = substr($querystring, 0, $pos);
        }
    
        /* 处理简短url */
        $pos = strpos($querystring, '-');
            $pos2 = strpos($querystring, '=');
        ($pos !== false) && $querystring = urlmap($querystring);
        
         ($pos2 !== false) && $querystring = urlmap($querystring);
        
        $andpos = strpos($querystring, "&");
        $andpos && $querystring = substr($querystring, 0, $andpos);
         $this->_querystring=$querystring;
        $this->get = explode('/', $querystring);
        if (empty($this->get[0])) {
            $this->get[0] = 'index';
        }
        if (empty($this->get[1])) {
            $this->get[1] = 'default';
        }

        
        if (count($this->get) < 2) {
            exit(' Access Denied !');
        }
        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
 
        $this->get = taddslashes($this->get, 1);
        $this->post = taddslashes(array_merge($_GET, $_POST));
      
       if($this->get[0]!='admin_template'){
        checkattack($this->post, 'post');
        checkattack($this->get, 'get');
         unset($_POST);
       }
       
    }

    function load_control() {
    	
        $controlfile = ASK2_APP_ROOT . '/control/' . $this->get[0] . '.php';
         $isapi = ('api' == substr($this->get[0], 0, 3));
       
         
           
          $isapi && $controlfile = ASK2_APP_ROOT . '/control/api/' . substr($this->get[0], 4) . '.php';
         $isapp = ('app' == substr($this->get[0], 0, 3));
          $ispccaiji = ('pccaiji' == substr($this->get[0], 0, 7));
          
           $ispccaiji && $controlfile = ASK2_APP_ROOT . '/control/pccaiji/' . substr($this->get[0], 8) . '.php';
          $isplugin = ('plugin' == substr($this->get[0], 0, 6));
            $isplugin && $controlfile = ASK2_APP_ROOT . '/control/plugin/' . substr($this->get[0], 7) . '.php';
          $isapp && $controlfile = ASK2_APP_ROOT . '/control/app/' . substr($this->get[0], 4) . '.php';
        $isadmin = ('admin' == substr($this->get[0], 0, 5));
        $isadmin && $controlfile = ASK2_APP_ROOT . '/control/admin/' . substr($this->get[0], 6) . '.php';

     
      
        if (false === include($controlfile)) {
            $this->notfound('control file "' . $controlfile . '" not found!');
        }
    }

    function run() {
        $controlname = $this->get[0] . 'control';
      
        $control = new $controlname($this->get, $this->post);
        $method = 'on' . $this->get[1];
        if (method_exists($control, $method)) {
            $regular = $this->get[0] . '/' . $this->get[1];
            $isajax = (0 === strpos($this->get[1], 'ajax'));
            
           
            if($control->whitelist){
            	$whitelist=explode(',', $control->whitelist);
            	$flag=in_array($this->get[1], $whitelist);
            	
            	
            }
           
            if ($control->checkable($regular,$this->_querystring) || $isajax ||!empty($flag)) {
                $control->$method();
            } else {
            	
                $control->message('您无权进行当前操作，原因如下：<br/> 您所在的用户组(' . $control->user['grouptitle'] . ')无法进行此操作。', 'user/login');
            }
        } else {
            $this->notfound('method "' . $method . '" not found!');
        }
    }

    function notfound($error) {
    	header("Location:".SITE_URL."index.php?index/notfound");

    }

}

?>