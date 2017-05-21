<?php

!defined('IN_ASK2') && exit('Access Denied');

class qiandaocontrol extends base {
	var $whitelist;
    function qiandaocontrol(& $get, & $post) {
        $this->base($get,$post);
       $this->whitelist="default,gethongbao";
    }
        function ondefault() {
            	$useragent = $_SERVER['HTTP_USER_AGENT']; 
    $wx=$this->fromcache('cweixin');
      
if (strstr($useragent, 'MicroMessenger')&&$wx['appsecret']!=''&&$wx['appsecret']!=null) { 
		
       	       
       	      
       	        $appid = $wx['appid'];
                $appsecret = $wx['appsecret'];

 require ASK2_ROOT . '/lib/php/jssdk.php';
$jssdk = new JSSDK($appid, $appsecret);
$signPackage = $jssdk->GetSignPackage();

}else{
	exit("请在微信公众号里签到，谢谢！");
}
        	 $navtitle ="圣诞节领红包活动";
          include template('qiandao');
        }
        
        
        function ongethongbao(){
        //入口文件
@require  ASK2_ROOT . '/lib/wxpay/hongbao/pay.php';
$packet = new Packet();
//获取用户信息
$get = $this->get[2];



$code = $_GET['code'];


//判断code是否存在
if($get=='access_token' && !empty($code)){
	$param['param'] = 'access_token';
	$param['code'] = $code;
	
	
	//获取用户openid信息
	$userinfo = $packet->_route('userinfo',$param);
	
	
	if(empty($userinfo['openid'])){
		exit("NOAUTH");
	}
	
	$ttime=time();
	if($ttime>="1482502056"){
		echo "还是要祝您节日快乐，红包活动已经结束啦，感谢参与!";
				exit();
	}
	$openid=$userinfo['openid'];
			$username=$userinfo['nickname'];
$one=$this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "weixin_hongbao WHERE opneid='$openid'");
			
			if($one){
				echo "您已经领取过红包了!";
				exit();
			}
	//调取支付方法
	$result=$packet->_route('wxpacket',array('openid'=>$userinfo['openid']));
//	
	switch ($result){
		case 'SUCCESS':
			
			
			
			
			 $this->db->query('INSERT INTO ' . DB_TABLEPRE . "weixin_hongbao(opneid,username,time) values ('$openid','$username','{$this->time}')");
      // echo 'INSERT INTO ' . DB_TABLEPRE . "weixin_hongbao(opneid,username,time) values ('$openid','$username','{$this->base->time}')";
        echo "红包领取成功!";
			 
			 break;
			default:
				echo "红包发放失败!";
				break;
	}
	exit();
}else{
	
	$packet->_route('userinfo');
}
        }
        
        
        
  
   
    
    
}