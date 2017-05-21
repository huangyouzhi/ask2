<?php

!defined('IN_ASK2') && exit('Access Denied');

class plugin_qianmingcontrol extends base {

    function plugin_qianmingcontrol(& $get, & $post) {
        $this->base($get,$post);
          $this->load('user');
    }

    function ondefault() {
    $this->load('user');
       include 'plugin/qianming/sign.php';
       
        
        $pagesize = $this->setting['list_default'];
       
       
         $rownum = $this->db->fetch_total('user', " signature='这个人很懒，什么都没留下' or signature is null");
         
          $pages = @ceil($rownum / $pagesize);
          
         
         $signlist= signature();
        
         $count=count($signlist);
      
         for($i=1;$i<=$count;$i++){
         	
         	 $page = max(1, intval($i));
         	  $startindex = ($page - 1) * $pagesize;
         
         	 $userlist = $_ENV['user']->get_active_list_bynosign($startindex, $pagesize);
            if( count($userlist)==0){
            	break;
            }
         foreach ($userlist as $user){
                	   
         	  	$user['signature']=$signlist[rand(0, $count-1)];
               
		$_ENV['user']->update($user['uid'],$user['gender'],$user['bday'],$user['phone'],$user['qq'],$user['msn'],$user['introduction'],$user['signature']);
	echo '更新<font color="red">'.$user['username'].'</font>的签名为:<font color="red">'.$user['signature']."</font><br>";
         }
         	
         }
         
    }

}

?>