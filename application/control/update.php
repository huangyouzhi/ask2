<?php

!defined('IN_ASK2') && exit('Access Denied');

class updatecontrol extends base {

    function updatecontrol(& $get, & $post) {
        $this->base($get, $post);
       $this->load('usergroup');
    }



    function ondefault() {
    	
    	header("Content-Type: text/html;charset=utf-8");
    	//---------
    	
    	$sql="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `appid` varchar(200) NOT NULL,
  `openid` varchar(200) NOT NULL,
  `mch_id` varchar(200) NOT NULL,
  `is_subscribe` varchar(100) NOT NULL,
  `nonce_str` varchar(200) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `sign` varchar(200) NOT NULL,
  `result_code` varchar(100) NOT NULL,
  `return_code` varchar(100) NOT NULL,
  `return_msg` varchar(100) NOT NULL,
  `trade_type` varchar(100) NOT NULL,
  `code_url` varchar(200) NOT NULL,
  `time` int(10) NOT NULL,
  `type` varchar(100) NOT NULL,
  `typeid` int(10) NOT NULL,
  `money` int(10) NOT NULL,
  `touid` int(10) NOT NULL,
   `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    	 $this->db->query($sql);  
    	       echo ' 更新成功:新增微信支付订单表<br>';
    	
    	           	$sql="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."user_tixian` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  
  `uid` int(10) NOT NULL,
  `jine` double NOT NULL,
  `state` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  `beizu` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    	 $this->db->query($sql);  
    	       echo ' 更新成功:新增用户提现表<br>';
    	       
    	$sql="ALTER TABLE  `ask_weixin_keywords` ADD  `title` VARCHAR( 200 ) NOT NULL ,
ADD  `content` TEXT NOT NULL ,
ADD  `fmtu` VARCHAR( 200 ) NOT NULL ,
ADD  `wzid` INT( 10) NOT NULL ,
ADD  `wburl` VARCHAR( 200 ) NOT NULL";
    	 $this->db->query($sql);  
       echo ' 更新成功:更新微信关键词表，增加图文标题，封面图片,图文内容和外部链接字段<br>';
    	
    	
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='baidu_api'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表baidu_api存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('baidu_api','')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加baidu_api<br>';
     }
    	
     
    		//---------------
    	
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='banner_color'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表banner_color存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('banner_color','#858c96')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加banner_color<br>';
     }
        		//---------------
    	     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='editor_choose'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表editor_choose存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('editor_choose','1')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加editor_choose<br>';
     }
        		//---------------
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='hct_logincode'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表hct_logincode存在<br>";
     }else{
     	$code=rand(11111111, 999999999);
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('hct_logincode',$code)";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加hct_logincode火车头采集文章的全局变量<br>';
     }
     
    	//---------------
    	
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='banner_img'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表banner_img存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('banner_img','https://gss0.bdstatic.com/7051cy89RcgCncy6lo7D0j9wexYrbOWh7c50/zhidaoribao/2016/0710/top.jpg')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加banner_img<br>';
     }
    	
    	
    	
    	
    	//-------------
    	try{
    	$groupid=2;
    	$regular_code=',user/sendcheckmail,user/editemail';
    	   $group = $_ENV['usergroup']->get($groupid);
    	   
    	  
    	  
    	 if(!strstr($group['regulars'], $regular_code)){
    	 	 $tmp=$group['regulars'].$regular_code;
    	  $group['regulars']=$tmp;
    	   $_ENV['usergroup']->update($groupid,$group);
    	 }
    	  
    	   
    	   	$groupid=3;
    	   	   $group = $_ENV['usergroup']->get($groupid);
    	 if(!strstr($group['regulars'], $regular_code)){
    	 	 $tmp=$group['regulars'].$regular_code;
    	  $group['regulars']=$tmp;
    	   $_ENV['usergroup']->update($groupid,$group);
    	 }
    	   	 
    	   	 for($i=7;$i<=26;$i++){
    	   	 	 $group = $_ENV['usergroup']->get($i);
    	   	  if(!strstr($group['regulars'], $regular_code)){
    	 	 $tmp=$group['regulars'].$regular_code;
    	  $group['regulars']=$tmp;
    	   $_ENV['usergroup']->update($i,$group);
    	 }
    	   	 }
    	}catch (Exception $e){
    		
    	}
    	
    	//-----------------
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='register_on'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表register_on存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('register_on','0')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加register_on<br>';
     }
    	//-----------------------------
    	  $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='hot_on'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表hot_on存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('hot_on','0')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加hot_on<br>';
     }
     
     //---------------------
        $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='title_description'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表title_description存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('title_description','知名专家为您解答')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加title_description<br>';
     }
     
     //------------------------------
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='search_shownum'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表search_shownum存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('search_shownum','5')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加search_shownum<br>';
     }
     
    	//------------------------------
     $sql_select_logo="select * from ". DB_TABLEPRE ."setting where k='site_logo'";
     
     $result_sitelogo=$this->db->query($sql_select_logo);
     $numlogo1=0;
           while($logo = $this->db->fetch_array($result_sitelogo)) {
           	
           $numlogo1= 	count($logo);
           }
  
     if($numlogo1>1){
     	echo "setting表site_logo存在<br>";
     }else{
     	$sql_sitelogo1="insert into ". DB_TABLEPRE ."setting  values('site_logo','站点别名')";
     	$this->db->query($sql_sitelogo1);
     	 echo ' 更新成功:更新setting表，增加site_logo<br>';
     }
     
     //--------------------------------------
         $sql_site_qrcode="select * from ". DB_TABLEPRE ."setting where k='site_qrcode'";
     
     $result_qrcode=$this->db->query($sql_site_qrcode);
     $numqrcode=0;
           while($qrcode = $this->db->fetch_array($result_qrcode)) {
           	
           $numqrcode= 	count($qrcode);
           }
  
     if($numqrcode>1){
     	echo "setting表site_qrcode存在<br>";
     }else{
     	$sql_qrcode="insert into ". DB_TABLEPRE ."setting  values('site_qrcode','站点别名')";
     	$this->db->query($sql_qrcode);
     	 echo ' 更新成功:更新setting表，增加site_qrcode<br>';
     }
     
     
     
     
     
         $sql_select_setting1="select * from ". DB_TABLEPRE ."setting where k='site_alias'";
     
     $result_setting1=$this->db->query($sql_select_setting1);
     $num1=0;
           while($user1 = $this->db->fetch_array($result_setting1)) {
           	
           $num1= 	count($user1);
           }
  
     if($num1>1){
     	echo "setting表site_alias存在<br>";
     }else{
     	$sql_setting1="insert into ". DB_TABLEPRE ."setting  values('site_alias','站点别名')";
     	$this->db->query($sql_setting1);
     	 echo ' 更新成功:更新setting表，增加site_alias<br>';
     }
       $sql_select_setting2="select * from ". DB_TABLEPRE ."setting where k='maxindex_keywords'";
     
     $result_setting2=$this->db->query($sql_select_setting2);
     $num2=0;
           while($user2 = $this->db->fetch_array($result_setting2)) {
           	
           $num2= 	count($user2);
           }
  
     if($num2>1){
     	echo "setting表maxindex_keywords,pagemaxindex_keywords存在<br>";
     }else{
     	$sql_setting2="insert into ". DB_TABLEPRE ."setting  values('maxindex_keywords','3'),('pagemaxindex_keywords','8')";
     	$this->db->query($sql_setting2);
     	 echo ' 更新成功:更新setting表，增加maxindex_keywords,pagemaxindex_keywords<br>';
     }
        //-----
      $sql_class1="alter table ".DB_TABLEPRE."topic add COLUMN likes int(10)  DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新topic表，增加likes字段<br>';

       
     //-----
      $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN activecode VARCHAR(200)  DEFAULT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加activecode字段<br>';
     //----
        $sql_class1="alter table ".DB_TABLEPRE."answer add COLUMN serverid VARCHAR(200) DEFAULT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新answer表，增加serverid字段<br>';
       
       //----askcity
       
          $sql_class1="alter table ".DB_TABLEPRE."question add COLUMN askcity VARCHAR(200) DEFAULT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新question表，增加askcity字段<br>';
     //----
        $sql_class1="alter table ".DB_TABLEPRE."answer add COLUMN openid VARCHAR(200) DEFAULT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新answer表，增加openid字段<br>';
     //----
      $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN openid VARCHAR(200) DEFAULT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加openid字段<br>';
     //----
            
      $sql_class1="alter table ".DB_TABLEPRE."answer add COLUMN voicetime int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新answer表，增加voicetime字段<br>';
       
       //-----
      $sql_class1="alter table ".DB_TABLEPRE."question add COLUMN hasvoice int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新question表，增加hasvoice字段<br>';
           //-----
      $sql_class1="alter table ".DB_TABLEPRE."question add COLUMN askuid int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新question表，增加askuid字段<br>';
                  //-----
      $sql_class1="ALTER TABLE  `".DB_TABLEPRE."category` ADD  `miaosu` text NOT NULL ,
ADD  `image` VARCHAR( 200 ) NOT NULL ,ADD  `followers` INT( 10 ) NOT NULL;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新category表，增加miao和followers，image字段<br>';
          //-----
      $sql_class1="ALTER TABLE  `".DB_TABLEPRE."category` ADD  `template` VARCHAR( 200 ) NOT NULL ;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新category表，增加template模板字段<br>';
       //-----
      $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN active int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加active字段<br>';
       //----
          $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN jine double DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加jine字段<br>';
         //----
          $sql_class1="alter table ".DB_TABLEPRE."question add COLUMN shangjin double DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新question表，增加shangjin字段<br>';
           //----
     
      $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN articles int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加articles字段<br>';
        $sql_class1="alter table ".DB_TABLEPRE."topic add COLUMN articles int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新topic表，增加articles字段<br>';
       
      $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN mypay double DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加mypay字段<br>';
          $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN fromsite int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加fromsite字段<br>';
     //------
      $sql_class1="alter table ".DB_TABLEPRE."answer add COLUMN reward DOUBLE DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新answer表，增加reward字段<br>';
         $sql_class1="alter table ".DB_TABLEPRE."user add COLUMN isblack int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新user表，增加isblack字段<br>';
       
       
             $sql_class1="alter table ".DB_TABLEPRE."usergroup add COLUMN doarticle int(10) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新usergroup表，增加doarticle字段<br>';
       
             $sql_class1="alter table ".DB_TABLEPRE."usergroup add COLUMN articlelimits int(10) DEFAULT 1;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新usergroup表，增加articlelimits字段<br>';
       
        
       
       
       
       
       
       
       
        //------
      $sql_class1="alter table ".DB_TABLEPRE."weixin_order add COLUMN prepay_id VARCHAR(200) DEFAULT 0;";
       $this->db->query($sql_class1);  
       echo ' 更新成功:更新weixin_order表，增加prepay_id字段<br>';
     //------
      $sql_class1="alter table ".DB_TABLEPRE."category add COLUMN alias VARCHAR(200) DEFAULT NULL;";
  $this->db->query($sql_class1);  
       echo ' 更新成功:更新category表，增加alias字段<br>';
       
       //-------
       
        $sql_bankcard="alter table ".DB_TABLEPRE."user add COLUMN bankcard VARCHAR(200) DEFAULT NULL;";
  $this->db->query($sql_bankcard);  
       echo ' 更新成功:更新category表，增加bankcard字段<br>';
       
       //-----
               $sql_bankcard="alter table ".DB_TABLEPRE."weixin_notify add COLUMN type VARCHAR(100) DEFAULT NULL;";
  $this->db->query($sql_bankcard);  
       echo ' 更新成功:更新category表，增加type字段<br>';
       
       
       //----
               $sql_bankcard="alter table ".DB_TABLEPRE."weixin_notify add COLUMN typeid int(10) DEFAULT NULL;";
  $this->db->query($sql_bankcard);  
       echo ' 更新成功:更新category表，增加typeid字段<br>';
       
       
       
       
       //-----
               $sql_bankcard="alter table ".DB_TABLEPRE."weixin_notify add COLUMN touid int(10) DEFAULT NULL;";
  $this->db->query($sql_bankcard);  
       echo ' 更新成功:更新category表，增加touid字段<br>';
          //-----
               $sql_bankcard="alter table ".DB_TABLEPRE."weixin_notify add COLUMN haspay int(10) DEFAULT 0;";
  $this->db->query($sql_bankcard);  
       echo ' 更新成功:更新category表，增加haspay字段<br>';
       //-------------------------------
       
       $sql_select_setting3="select * from ". DB_TABLEPRE ."setting where k='openweixin'";
     
     $result_setting3=$this->db->query($sql_select_setting3);
     $num3=0;
           while($user3 = $this->db->fetch_array($result_setting3)) {
           	
           $num3= 	count($user3);
           }
  
     if($num3>1){
     	echo "setting表openweixin存在<br>";
     }else{
     	$sql_setting3="insert into ". DB_TABLEPRE ."setting  values('openweixin','0')";
     	$this->db->query($sql_setting3);
     	 echo ' 更新成功:更新setting表，增加maxindex_keywords,pagemaxindex_keywords<br>';
     }
     
     
    
       
       //----------------------------
      //表面前缀:DB_TABLEPRE
     //1 更新setting表，增加tpl_wapdir，wap_domain
     //tpl_wapdir表示wap模板的文件夹名字  wap_domain表示手机站域名
     //查询是否存在字段
     $sql_select_setting="select * from ". DB_TABLEPRE ."setting where k='tpl_wapdir'";
     
     $result_setting=$this->db->query($sql_select_setting);
     $num=0;
           while($user = $this->db->fetch_array($result_setting)) {
           	
           $num= 	count($user);
           }
  
     if($num>1){
     	echo "setting表tpl_wapdir，wap_domain存在<br>";
     }else{
     	$sql_setting="insert into ". DB_TABLEPRE ."setting  values('tpl_wapdir','wap'),('wap_domain','')";
     	$this->db->query($sql_setting);
     	 echo '1 更新成功:更新setting表，增加tpl_wapdir，wap_domain<br>';
     }
     
     //---
//     --
//-- 表的结构 `ask_favorite`
//--

$sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."topic_likes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;";

     $this->db->query($sql);
     	 echo '更新成功:增加 topic_likes表<br>';
     	//     --
//-- 表的结构 `topic_viewhistory`
//--

$sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."topic_viewhistory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
   `username` varchar(200) NOT NULL,
  `tid` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;";

     $this->db->query($sql);
     	 echo '更新成功:增加 topic_viewhistory表<br>'; 
     //----------------------
     //2  增加管理员分类表 category_admin
     
     $sql_category_admin="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."category_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
     ";
     
     $this->db->query($sql_category_admin);
     	 echo '2 更新成功:增加 category_admin表<br>';
     	 
     	 //---
     	 
   $sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."categotry_follower` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

     $this->db->query($sql);
     echo '2 更新成功:增加 categotry_follower表<br>';
     	//---
     	$sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."paylog` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `money` double NOT NULL,
  `openid` varchar(200) NOT NULL,
  `fromuid` int(10) NOT NULL,
  `touid` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  `typeid` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
     	 $this->db->query($sql);
     	 echo '2 更新成功:增加 paylog支付流水表<br>';
     	 //----
     	 
     	 $sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."user_depositmoney` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `needpay` double NOT NULL,
  `type` varchar(100) NOT NULL,
  `typeid` int(10) NOT NULL,
  `fromuid` int(10) NOT NULL,
    `state` int(10) NOT NULL default 0,
     `touid` int(10) NOT NULL ,
  `time` int(10) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
     	 $this->db->query($sql);
     	 echo '2 更新成功:增加 user_depositmoney托管资金表<br>';
     	 //------
     	 $sql="
     	  CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."articlecomment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` char(50) NOT NULL,
  `author` varchar(15) NOT NULL DEFAULT '',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `adopttime` int(10) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL,
  `comments` int(10) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ip` varchar(20) DEFAULT NULL,
  `supports` int(10) NOT NULL DEFAULT '0',
  `reward` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `authorid` (`authorid`),
  KEY `adopttime` (`adopttime`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
     	  $this->db->query($sql);
     	 echo '2 更新成功:增加 articlecomment文章评论表<br>';
     	 //-------------------------
     	 //weixin_notify 支付通知表
     	 $sql="
     	 

CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_notify` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `appid` varchar(200) NOT NULL,
  `attach` varchar(200) NOT NULL,
  `bank_type` varchar(50) NOT NULL,
  `cash_fee` varchar(100) NOT NULL,
  `fee_type` varchar(100) NOT NULL,
  `is_subscribe` varchar(50) NOT NULL,
  `mch_id` varchar(200) NOT NULL,
  `nonce_str` varchar(200) NOT NULL,
  `openid` varchar(200) NOT NULL,
  `out_trade_no` varchar(200) NOT NULL,
  `result_code` varchar(200) NOT NULL,
  `return_code` varchar(100) NOT NULL,
  `return_msg` varchar(100) NOT NULL,
  `sign` varchar(200) NOT NULL,
  `time_end` int(10) NOT NULL,
  `total_fee` int(10) NOT NULL,
  `trade_state` varchar(100) NOT NULL,
  `trade_type` varchar(100) NOT NULL,
  `transaction_id` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
     	 ";
     	 $this->db->query($sql);
     	 echo '2 更新成功:增加 weixin_notify表<br>';
     	 
     	 
     	 
     	//----------------------------------- 
     	 $sql_userbank="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."userbank` (
  `id` int(10) NOT NULL,
  `fromuid` int(10) NOT NULL,
  `touid` int(10) NOT NULL,
  `operation` varchar(200) NOT NULL,
   `money` int(10) NOT NULL,
  `time` int(11) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
     	 
      $this->db->query($sql_userbank);
     	 echo '2 更新成功:增加 userbank表<br>';	 
     	//------------------------------ 
   $sqlkeywords="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."keywords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `find` varchar(200) NOT NULL,
  `replacement` varchar(200) NOT NULL,
  `admin` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;
   ";
     	  $this->db->query($sqlkeywords);
     	 echo '------ 更新成功:增加 keywords表<br>';
     	 //3 inform修改 
     	 
     	 
     	 //-------------------
     	 
     	 $sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `msg` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
     	 
     	 
     	   $this->db->query($sql);
     	 echo '------ 更新成功:增加 weixin_info表<br>';
     	 //-----------------------------------------------------
     	 $sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_follower` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openid` varchar(200) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `language` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `headimgurl` varchar(200) NOT NULL,
  `privilege` varchar(200) NOT NULL,
  `unionid` varchar(200) NOT NULL,
  `sex` varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
     	 
     	 $this->db->query($sql);
     	 echo '------ 更新成功:增加 weixin_follower表<br>'; 
     	 
     	 //------------------------------------------
     	 
     	 $sql="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(200) NOT NULL,
  `menu_type` varchar(200) NOT NULL,
  `menu_keyword` varchar(200) NOT NULL,
  `menu_link` varchar(200) NOT NULL,
  `menu_pid` int(10) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
     	 
     	  $this->db->query($sql);
     	 echo '------ 更新成功:增加 weixin_menu表<br>';
     	 //-----------------------------
     	 $sql="
     	 CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `wxname` varchar(200) NOT NULL,
  `wxid` varchar(200) NOT NULL,
  `weixin` varchar(200) NOT NULL,
  `appid` varchar(200) NOT NULL,
  `appsecret` varchar(200) NOT NULL,
  `winxintype` varchar(200) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;";
     	 
     	   $this->db->query($sql);
     	 echo '------ 更新成功:增加 weixin_setting表<br>';
     	 
     	 //---------------------------------------
     	 $sql="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."weixin_keywords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `txtname` varchar(200) NOT NULL,
  `txtcontent` varchar(200) NOT NULL,
  `txttype` varchar(200) NOT NULL,
  `showtype` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
     	 
     	   $this->db->query($sql);
     	 echo '------ 更新成功:增加 weixin_keywords表<br>';
     	 
     	 
     	 
     	 
     	 //创建站点日志表
     	 	 
     	  $site_log="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."site_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `guize` varchar(200) NOT NULL,
   `miaoshu` varchar(200)  NULL,
   `uid` int(10)  NULL,
     `username` varchar(200) NOT NULL,
  `time` int(10) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
     	 
     	   $this->db->query($site_log);  
     	   echo " site_log站点日志表插入成功<br>";
     	 
     	 
     	 
     	 
     	 
     	 
     	 //----------------------------
     	 //删除 inform DROP TABLE IF EXISTS t_bd_shop_bi;
     	 $sql_inform='DROP TABLE IF EXISTS '.DB_TABLEPRE.'inform;';
     	 
     	  $this->db->query($sql_inform);
     	  
     	  $sql_create_inform="
     	  
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."inform` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) NOT NULL,
  `uid` int(10) NOT NULL,
  `qtitle` varchar(200) NOT NULL,
  `qid` int(100) NOT NULL,
  `aid` int(11) NOT NULL,
  `content` text NOT NULL,
  `title` varchar(100) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `counts` int(11) NOT NULL DEFAULT '1',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;";
     	 
     	 $this->db->query($sql_create_inform);  
     	 echo '3 更新INform成功<br>';
     // 4 更新 topic专题变成文章
      $sql_topicprice="alter table ".DB_TABLEPRE."topic add COLUMN price  int(10) DEFAULT 0;";
    	 $sql_topic1="alter table ".DB_TABLEPRE."topic add COLUMN author VARCHAR(200) DEFAULT NULL;";
     $sql_topic2="alter table ".DB_TABLEPRE."topic add COLUMN authorid int(10) DEFAULT 1;";
     	 $sql_topic3="alter table ".DB_TABLEPRE."topic add COLUMN views int(10) DEFAULT 1;";
     	 $sql_topic4="alter table ".DB_TABLEPRE."topic add COLUMN articleclassid int(10) DEFAULT 1;";
     	  $sql_topic5="alter table ".DB_TABLEPRE."topic add COLUMN isphone int(10) DEFAULT 0;";
 	  $sql_topic6="alter table ".DB_TABLEPRE."topic add COLUMN viewtime int(10) DEFAULT 0;";
 	   $sql_topic7="alter table ".DB_TABLEPRE."topic add COLUMN ispc int(10) DEFAULT 0;";
      $sql_editcontent= "ALTER TABLE  `".DB_TABLEPRE."topic` CHANGE  `describtion`  `describtion` TEXT  DEFAULT NULL";
    
      
       $this->db->query($sql_topicprice); 
   $this->db->query($sql_editcontent);  
      $this->db->query($sql_topic1);  
     	   $this->db->query($sql_topic2);  
     	    $this->db->query($sql_topic3);  
     	     $this->db->query($sql_topic4);  
     	      $this->db->query($sql_topic5);  
     	       $this->db->query($sql_topic6);  
     	         $this->db->query($sql_topic7);  
     	  echo '4 更新topic表成功<br>';      
     	 //5 插入表
     	 
     	  $topic_tag="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."topic_tag` (
  `aid` int(10) NOT NULL,
  `name` varchar(200) NOT NULL,
  `time` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
     	  ";
     	   $this->db->query($topic_tag);  
     	   echo "4.1 topic_tag文章标签表插入成功<br>";
     	   
     	   //--------------------顶置表
     	   
     	     	
     	 
     	  $topdata="CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."topdata` (
     	  `id` int(10) NOT NULL AUTO_INCREMENT,
  `typeid` int(10) NOT NULL,
  `type` varchar(200) NOT NULL,
   `order` int(10) NOT NULL DEFAULT '1',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
     	  ";
     	   $this->db->query($topdata);  
     	   echo " topdata文章标签表插入成功<br>";
     	   
     	   
     	   
     	   //===========================
     	  $cat_topic="
CREATE TABLE IF NOT EXISTS `".DB_TABLEPRE."topicclass` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `dir` varchar(200) NOT NULL,
  `pid` int(10) NOT NULL,
  `displayorder` int(10) NOT NULL,
  `articles` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
     	  ";
     	   $this->db->query($cat_topic);  
     	   echo "5 topicclass表插入成功<br>";
     	   
     	      $this->config_edit();
    }
    
    
    function config_edit() {
	extract($GLOBALS, EXTR_SKIP);
	$dbhost=DB_HOST;
	$dbuser=DB_USER;
	$dbpw=DB_PW;
	$dbname=DB_NAME;
		$version='3.5';
	$tablepre=DB_TABLEPRE;
	  $versiondate=date("Ymd");
	define('CONFIG', ASK2_ROOT.'/config.php');
	$config = "<?php \r\ndefine('DB_HOST', '$dbhost');\r\n";
	$config .= "define('DB_USER', '$dbuser');\r\n";
	$config .= "define('DB_PW', '$dbpw');\r\n";
	
	$config .= "define('DB_NAME', '$dbname');\r\n";
	$config .= "define('DB_CHARSET', 'utf8');\r\n";
	$config .= "define('DB_TABLEPRE', '$tablepre');\r\n";
	$config .= "define('DB_CONNECT', 0);\r\n";
	$config .= "define('ASK2_CHARSET', 'UTF-8');\r\n";
	$config .= "define('ASK2_VERSION', '$version');\r\n";
	$config .= "define('ASK2_RELEASE', '$versiondate');\r\n";
	$fp = fopen(CONFIG, 'w');
	fwrite($fp, $config);
	fclose($fp);
	exit("重新配置成功");
}
    

}

?>