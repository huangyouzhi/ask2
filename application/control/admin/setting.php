<?php
 ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Win64; x64; .NET CLR 2.0.50727; SLCC1; Media Center PC 5.0; .NET CLR 3.0.30618; .NET CLR 3.5.30729)');
!defined('IN_ASK2') && exit('Access Denied');

class admin_settingcontrol extends base {

    function admin_settingcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("tag");
          $this->load('user');
        $this->load('setting');
         $this->load("question");
        $this->load("doing");
          $this->load("category");
           $this->load('answer');
        $this->load('answer_comment');
    }

    function ondefault() {
        $this->onbase();
    }

    /* 基本设置 */

    function onbase() {
    	  $themetpllist = $_ENV['setting']->tpl_themelist();
        $tpllist = $_ENV['setting']->tpl_list();
            $waptpllist = $_ENV['setting']->tpl_waplist();
          
        if (isset($this->post['submit'])) {
        	// runlog("11bg.txt", $this->post['banner_color']);
           if (isset($_FILES["file_upload"])) {
              $imgname = strtolower($_FILES['file_upload']['name']);
                
            $type = substr(strrchr($imgname, '.'), 1);
            
            if (isimage($type)) {
            	
            	
            	
            	 $upload_tmp_file = ASK2_ROOT . '/data/tmp/sitelogo.'. $type;

            $filepath ='/data/attach/logo/logo' . '.' . $type;
            forcemkdir(ASK2_ROOT . '/data/attach/logo');
               if (move_uploaded_file($_FILES['file_upload']['tmp_name'], ASK2_ROOT.$filepath)) {
               	unlink($filepath);
                image_resize($upload_tmp_file,  $filepath, 172, 60);
               // move_uploaded_file($upload_tmp_file,ASK2_ROOT. $filepath);
                  try{
           $this->setting['site_logo']=SITE_URL.substr($filepath, 1);
             }catch (Exception $e){
                	print $e->getMessage();  
                }
}else{
	 
}

               
               }
            }
        }
               if (isset($_FILES["bannerfile"])) {
               	$bannerfile = strtolower($_FILES['bannerfile']['name']);
               	  $bannertype = substr(strrchr($bannerfile, '.'), 1);
               
            
            if (isimage($bannertype)) {
            	
            	
            	
            	 $upload_tmp_file = ASK2_ROOT . '/data/tmp/sitebanner.'. $bannertype;

            $filepath = '/data/attach/banner/sitebanner' . '.' . $bannertype;
            forcemkdir(ASK2_ROOT . '/data/attach/banner');
               if (move_uploaded_file($_FILES['bannerfile']['tmp_name'], $upload_tmp_file)) {
               	
               	
                image_resize($upload_tmp_file, ASK2_ROOT . $filepath, 1180, 400);
                  try{
                 $this->setting['banner_img']=SITE_URL.substr($filepath, 1);
              }catch (Exception $e){
                	print $e->getMessage();  
                }


               
               
            }
    }
           
                $this->setting['banner_color'] = $this->post['banner_color'];
              
            $this->setting['site_name'] = $this->post['site_name'];
              $this->setting['seo_index_title'] = $this->post['seo_index_title'];
               $this->setting['openweixin'] = $this->post['openweixin'];
            $this->setting['register_clause'] = $this->post['register_clause'];
            $this->setting['site_icp'] = $this->post['site_icp'];
            $this->setting['verify_question'] = $this->post['verify_question'];
            $this->setting['allow_outer'] = $this->post['allow_outer'];
            $this->setting['tpl_dir'] = $this->post['tpl_dir'];
             $this->setting['jingyan'] = $this->post['jingyan'];
              $this->setting['hct_logincode'] = $this->post['hct_logincode'];
             
                $this->setting['tpl_themedir'] = $this->post['tpl_themedir'];
              $this->setting['tpl_wapdir'] = $this->post['tpl_wapdir'];
               $this->setting['wap_domain'] = $this->post['wap_domain'];
            $this->setting['question_share'] =stripslashes( $this->post['question_share']);
            $this->setting['site_statcode'] = stripslashes($this->post['site_statcode']);
            $this->setting['index_life'] = $this->post['index_life'];
            $this->setting['sum_category_time'] = $this->post['sum_category_time'];
            $this->setting['sum_onlineuser_time'] = $this->post['sum_onlineuser_time'];
            $this->setting['list_default'] = $this->post['list_default'];
            $this->setting['rss_ttl'] = $this->post['rss_ttl'];
            $this->setting['code_register'] = intval(isset($this->post['code_register']));
            
             $this->setting['cancopy'] = intval($this->post['cancopy']);
            $this->setting['code_login'] = intval(isset($this->post['code_login']));
            $this->setting['code_ask'] = intval(isset($this->post['code_ask']));
            $this->setting['code_message'] = intval(isset($this->post['code_message']));
            $this->setting['notify_mail'] = intval(isset($this->post['notify_mail']));
            $this->setting['notify_message'] = intval(isset($this->post['notify_message']));
            $this->setting['allow_expert'] = intval($this->post['allow_expert']);
            $this->setting['apend_question_num'] = intval($this->post['apend_question_num']);
            $this->setting['allow_credit3'] = intval($this->post['allow_credit3']);
            $overdue_days = intval($this->post['overdue_days']);
            if ($overdue_days && $overdue_days >= 3) {
                $this->setting['overdue_days'] = $overdue_days;
                $_ENV['setting']->update($this->setting);
                $message = '站点设置更新成功！';
            } else {
                $type = "errormsg";
                $message = '问题过期时间至少为3天！';
            }
        }
        include template('setting_base', 'admin');
    }

    /* 时间设置 */

    function ontime() {
        $timeoffset = array(
            '-12' => '(标准时-12:00) 日界线西',
            '-11' => '(标准时-11:00) 中途岛、萨摩亚群岛',
            '-10' => '(标准时-10:00) 夏威夷',
            '-9' => '(标准时-9:00) 阿拉斯加',
            '-8' => '(标准时-8:00) 太平洋时间(美国和加拿大)',
            '-7' => '(标准时-7:00) 山地时间(美国和加拿大)',
            '-6' => '(标准时-6:00) 中部时间(美国和加拿大)、墨西哥城',
            '-5' => '(标准时-5:00) 东部时间(美国和加拿大)、波哥大',
            '-4' => '(标准时-4:00) 大西洋时间(加拿大)、加拉加斯',
            '-3.5' => '(标准时-3:30) 纽芬兰',
            '-3' => '(标准时-3:00) 巴西、布宜诺斯艾利斯、乔治敦',
            '-2' => '(标准时-2:00) 中大西洋',
            '-1' => '(标准时-1:00) 亚速尔群岛、佛得角群岛',
            '0' => '(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡',
            '1' => '(标准时+1:00) 中欧时间、安哥拉、利比亚',
            '2' => '(标准时+2:00) 东欧时间、开罗，雅典',
            '3' => '(标准时+3:00) 巴格达、科威特、莫斯科',
            '3.5' => '(标准时+3:30) 德黑兰',
            '4' => '(标准时+4:00) 阿布扎比、马斯喀特、巴库',
            '4.5' => '(标准时+4:30) 喀布尔',
            '5' => '(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇',
            '5.5' => '(标准时+5:30) 孟买、加尔各答、新德里',
            '6' => '(标准时+6:00) 阿拉木图、 达卡、新亚伯利亚',
            '7' => '(标准时+7:00) 曼谷、河内、雅加达',
            '8' => '(标准时+8:00)北京、重庆、香港、新加坡',
            '9' => '(标准时+9:00) 东京、汉城、大阪、雅库茨克',
            '9.5' => '(标准时+9:30) 阿德莱德、达尔文',
            '10' => '(标准时+10:00) 悉尼、关岛',
            '11' => '(标准时+11:00) 马加丹、索罗门群岛',
            '12' => '(标准时+12:00) 奥克兰、惠灵顿、堪察加半岛');
        if (isset($this->post['submit'])) {
            $this->setting['time_offset'] = $this->post['time_offset'];
            $this->setting['time_diff'] = $this->post['time_diff'];
            $this->setting['date_format'] = $this->post['date_format'];
            $this->setting['time_format'] = $this->post['time_format'];
            $this->setting['time_friendly'] = $this->post['time_friendly'];
            $_ENV['setting']->update($this->setting);
            $message = '时间设置更新成功！';
        }
        include template('setting_time', 'admin');
    }

    /* 列表显示 */

    function onlist() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('list' == substr($key, 0, 4)) {
                    $this->setting[$key] = $value;
                }
            }
               $this->setting['title_description'] =$this->post['title_description'];
                  $this->setting['hot_on'] = intval($this->post['hot_on']);
            $this->setting['index_life'] = intval($this->post['index_life']);
            $this->setting['hot_words'] = $_ENV['setting']->get_hot_words($this->setting['list_hot_words']);
            $_ENV['setting']->update($this->setting);
            $message = '列表显示更新成功！';
        }
        include template('setting_list', 'admin');
    }
//关注问题
function attention_question($qid,$user_uid,$user_username){
	$uid=$user_uid;
	$username=$user_username;
    $is_followed = $_ENV['question']->is_followed($qid,$uid);
        if ($is_followed) {
            $_ENV['user']->unfollow($qid, $uid);
        } else {
            $_ENV['user']->follow($qid, $uid,$username);
          
           
           
          
            $_ENV['doing']->add($uid, $username, 4, $qid);
        }
}
function rand_time($a,$b)
{
$a=strtotime($a);
$b=strtotime($b);
return date( "Y-m-d H:m:s", mt_rand($a,$b));
}

   function ongetoncaiji(){
    	$result=array();
    	require 'simple_html_dom.php';
    	$title=strip_tags($_REQUEST["title"]) ;// $_POST["title"];
    	 	$caiji_url= $_REQUEST["daanurl"];
   	 	$caiji_prefix= $_REQUEST["guize"];
   	 	$caiji_desc= $_REQUEST["daandesc"];
   	 	$caiji_best= $_REQUEST["daanbest"];
   	 	$caiji_hdusertx= $_REQUEST["caiji_hdusertx"];
   	 	$caiji_hdusername= $_REQUEST["caiji_hdusername"];
    	 		$bianma=$_REQUEST["bianma"];
    	 		$ckabox=$_REQUEST["ckabox"];
    	 		$imgckabox=$_REQUEST["imgckabox"];
    	 		
    	 		$result['ckabox']=$ckabox;
    	 		$result['imgckabox']=$imgckabox;
    	 	 $html = file_get_html($caiji_url);
    	
    	 	 try{
    	 	 $desc='';
    	 	 if($caiji_desc!=''){
    	 	 $wtdesc= $html->find($caiji_desc);
    
    	 	$desc= $wtdesc[0]->outertext ;
    	 	  $suffer= substr($desc, 0,4);
    	 	if($suffer=='<pre'){
    	 		$desc=$wtdesc[0]->plaintext   ;
    	 	}
    	 	
    	 	 }
    	 	 
    	 	
    	 	$q= $_ENV['question']->get_by_title(htmlspecialchars($title));
    	 	 if($q!=null)
    	 	 {
    	 	 	$result['result']='1';
    	 	 }else{
    	 	 	$result['result']='0';
    	 	 }
    	 	 
    	 	// $desc=htmlspecialchars($desc);
    	 	  
          
    
				$desc=str_replace('<img class=">ͼ"', '', $desc);
					 $desc= str_replace('<img class=">图" class="ikqb_img_alink', '', $desc);
					 
					 
    	 	 //if($ckabox=='true'||$ckabox=='on'){
	 //$desc=filter_outer($desc);
//}
    	 	// if($imgckabox=='true'||$imgckabox=='on'){
	 //$desc=filter_imgouter($desc);
//}

    	$result['miaosu']=$desc;//问题描述
    	
    	      $wtbest= $html->find($caiji_best);
    	 $atest= $wtbest[0]->outertext ;
    	 
    	 if($ckabox=='true'||$ckabox=='on'){
	
	$atest=preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $atest);
         }
    	 	 if($imgckabox=='true'||$imgckabox=='on'){
	  
	   $atest=preg_replace('/<img[^>]+>/i','',$atest);
}
    	 $result['bestanswer']=$atest;//最佳答案
    	 
    	 	  if($imgckabox=='true'||$imgckabox=='on'){
    	 	 	
	 $result['guolvtupuan']='过滤图片';//过滤图片
}
    	 //其它回答
    	 $type_fill = $html->find($caiji_prefix);
    	 $result['otherlist']=array();
    	 $count1=0;
    	 foreach ($type_fill as $r) {
    	 	if($bianma=='gb2312'){
  $caijilist[$count1]['title']=iconv('gb2312', 'utf-8',$r->outertext)  ;
	}else{
			$str= $r->outertext  ;
		$str=str_replace("'", "", $str);
		$str=str_replace('<pre style="font-family:微软雅黑;">', "", $str);
				$str=str_replace('</pre>', "", $str);
				
				
		$caijilist[$count1]['title']=$str ;
		//$caijilist[$count1]['title']= $r->outertext  ;
	}
    	  if($ckabox=='true'||$ckabox=='on'){
	// $caijilist[$count1]['title']=filter_outer($caijilist[$count1]['title']);
	$caijilist[$count1]['title']=preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $caijilist[$count1]['title']);
}
    	 	 if($imgckabox=='true'||$imgckabox=='on'){
    	 	 $caijilist[$count1]['title']=preg_replace('/<img[^>]+>/i','',$caijilist[$count1]['title']);	
	// $caijilist[$count1]['title']=filter_imgouter($caijilist[$count1]['title']);
}
	array_push($result['otherlist'], $caijilist[$count1]['title']);
	 $count1++;
    	 }
    	 
    	 	 }catch (Exception $err){
    	 	 	
    	 	 }
    	
    	
    	 $html->clear();
    	
    	
    	echo json_encode($result);
    	exit();
    }
    
function onputcaiji(){
	$result=array();
		$json=json_decode( $_REQUEST["jsonstring"],true);
		$title=$json['m_title'];
		$desc=$json['m_miaosu'];
		$tiwentime=$json['m_tiwentime'];
    	$huidatime=$json['m_huidatime'];
    	$ckabox=$_REQUEST["ckabox"];
    	$imgckabox=$_REQUEST["imgckabox"];
$randclass=$json["randclass"];
    	$cid=$json["cid"];
    	


    	
    	$cid1=$json["cid1"];
    	$cid2=$json["cid2"];
    	$cid3=$json["cid3"];
    	
    		// $catlist=$_ENV['category']->list_by_pid($catmodel['pid']);
    	if(trim($randclass)!=''){
    		
    		$classarray=explode(',', $randclass);
    		 $cidindex=array_rand($classarray,1);
    	$cid=$classarray[$cidindex];
    		  $catlist=$_ENV['category']->get($cid);
    		  if($catlist['pid']==0){
    		  	 $cid1=$cid;
    		  }else{
    		  	$cid1=$catlist['pid'];
    		  	$cid2=$cid;
    		  }
    		   
    		    
    		    $cid3=3;
    	}
	 if($title==""||$title==null){
    	 	 	
    	 	 	$result['result']="-1";
    	 	 	echo json_encode($result);
    	 	 	exit();
    	 	 }
    	 	$q= $_ENV['question']->get_by_title(htmlspecialchars($title));
    	 	 if($q!=null)
    	 	 {
    	 	 	$result['result']="0";
    	 	 	echo json_encode($result);
    	 	 	exit();
    	 	 }
	//插入标题和描述
	 $nowtime=date("Y-m-d H:i:s");
$tiwentime=$tiwentime*60;
 $randtime=rand(1,$tiwentime);
         
    	 $t1=date('Y-m-d H:i:s',strtotime("-$randtime minute"));//"2015-1-29 08:43:21";

            $mtime=strtotime( $t1);
	 $userlist = $_ENV['user']->get_caiji_list(0, 30);
    	 $mwtuid=array_rand($userlist,1);
    	 $uid=$userlist[$mwtuid]['uid'];
    	 $username=$userlist[$mwtuid]['username'];
    	  try{
                require_once ASK2_STATIC_ROOT.'/js/neweditor/php/Config.php';
                if(Config::OPEN_OSS){
    	     	 require_once ASK2_STATIC_ROOT.'/js/neweditor/php/up.php';
 if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($desc);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$desc=str_replace($img_arr[1][$i],$img_url, $desc);
}
    	 	   	 	 }	

}
                }
                 }catch (Exception $e){
                	print $e->getMessage();  
                }
                  	 $desc=str_replace("'", '"', $desc);
	   	 	 $qid = $_ENV['question']->add_seo(htmlspecialchars($title),$uid,$username,$desc, 0, rand(0, 100), $cid, $cid1, $cid2, $cid3, 1,rand(10, 200),$mtime);

    	 	 $numuser=rand(3, 5);
    	 	 for($i=0;$i<=$numuser;$i++){
    	 	 	 $auid=array_rand($userlist,1);
    	 $_uid=$userlist[$auid]['uid'];
    	 $_username=$userlist[$auid]['username'];
    	 	 	 $this->attention_question($qid,$_uid,$_username);
    	 	 }
    	 	
    
        
        $taglist=dz_segment(htmlspecialchars($title));
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
    	 	 
    	 	 $_ENV['doing']->add($uid, $username, 1, $qid, '');
    	 	 
    	 
$caijilist=array();
$count1=0;
$commentarr=array('真给力',"谢谢你",'非常感谢你','你真是个大好人','你真的帮了我大忙','高手留个联系方式吧','大神......');
$comment=$commentarr[array_rand($commentarr,1)];


 $num=1;
$aid=0;	 
//如果最佳答案存在不为空就设置这个最佳答案
if(!empty($json["m_q_best"])&&$json["m_q_best"]!=''){
	
	  $randtime=rand(1,$huidatime);
 $t2=date('Y-m-d H:i:s',strtotime("-$randtime minute"));
 


            $mtime=strtotime( $t2);
$quid=array_rand($userlist,1);  	 	 
  $_buid=$userlist[$quid]['uid'];
    	 $_busername=$userlist[$quid]['username'];  
 if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($json["m_q_best"]);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$json["m_q_best"]=str_replace($img_arr[1][$i],$img_url, $json["m_q_best"]);
}
    	 	   	 	 }	

}
$json["m_q_best"]=str_replace("'", '"', $json["m_q_best"]);
 $aid= $_ENV['answer']->add_seo($qid,$title,$json["m_q_best"],$_buid,$_busername,1,rand(20, 80),$mtime);
  unset($userlist[array_search($_busername,$userlist)]); 


  $answer = $_ENV['answer']->get($aid);
 

      	
      	 $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
        $_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	    $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid,$_buid, $title);
        }
}

    	//采集其它回答
foreach ($json['m_q_other'] as $k=>$v)
{
   $acontent =$v['content'];
	
   if(empty($acontent)&&$acontent==''){
   	continue;
   }
	if(strstr($acontent,'<span>热心卡友</span>')){
		continue;
	}
	  $randtime=rand(1,$huidatime);
 $t2=date('Y-m-d H:i:s',strtotime("-$randtime minute"));
 


            $mtime=strtotime( $t2);
$quid=array_rand($userlist,1);  	 	 
  $_buid=$userlist[$quid]['uid'];
    	 $_busername=$userlist[$quid]['username'];  
 if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($acontent);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$acontent=str_replace($img_arr[1][$i],$img_url, $acontent);
}
    	 	   	 	 }	

}
$acontent=str_replace("'", '"', $acontent);
 $aid= $_ENV['answer']->add_seo($qid,$title,$acontent,$_buid,$_busername,1,rand(20, 80),$mtime);
  unset($userlist[array_search($_busername,$userlist)]); 


 
 

      	
     
       
}
$result['result']="1";
echo json_encode($result);
    	 	 	exit();
}


     /* ajax插入数据 */

    function onajaxcaiji() {
    	$title=strip_tags($_REQUEST["title"]) ;// $_POST["title"];
    	$tiwentime=strip_tags($_REQUEST["tiwentime"]) ;// $_POST["title"];
    	$huidatime=strip_tags($_REQUEST["huidatime"]) ;// $_POST["title"];
    	$randclass=$_REQUEST["randclass"];
    	$cid=$_REQUEST["cid"];
    	
    	$caiji_beginnum=$_REQUEST["caiji_beginnum"];
    	$caiji_endnum=$_REQUEST["caiji_endnum"];
    	
    	$cid1=$_REQUEST["cid1"];
    	$cid2=$_REQUEST["cid2"];
    	$cid3=$_REQUEST["cid3"];
    	
    		// $catlist=$_ENV['category']->list_by_pid($catmodel['pid']);
    	if(trim($randclass)!=''){
    		
    		$classarray=explode(',', $randclass);
    		 $cidindex=array_rand($classarray,1);
    	$cid=$classarray[$cidindex];
    		  $catlist=$_ENV['category']->get($cid);
    		  if($catlist['pid']==0){
    		  	 $cid1=$cid;
    		  }else{
    		  	$cid1=$catlist['pid'];
    		  	$cid2=$cid;
    		  }
    		   
    		    
    		    $cid3=3;
    	}
    	$uid=$_REQUEST["uid"];
    	$ckbox=$_REQUEST["ckbox"];
    	$username=$_REQUEST["username"];
    	$page = max(1, intval($this->get[2]));
    	$pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
    	 $userlist = $_ENV['user']->get_caiji_list($startindex, $pagesize);
    	 $mwtuid=array_rand($userlist,1);
    	 $uid=$userlist[$mwtuid]['uid'];
    	 $username=$userlist[$mwtuid]['username'];
    	// unset($userlist[array_search($username,$userlist)]); 
 $nowtime=date("Y-m-d H:i:s");
$tiwentime=$tiwentime*60;
 $randtime=rand(1,$tiwentime);
         
    	 $t1=date('Y-m-d H:i:s',strtotime("-$randtime minute"));//"2015-1-29 08:43:21";

            $mtime=strtotime( $t1);
//include 'lib/simple_html_dom.php';
    	 	require 'simple_html_dom.php';
    	 	$caiji_url= $_REQUEST["daanurl"];
   	 	$caiji_prefix= $_REQUEST["guize"];
   	 	$caiji_desc= $_REQUEST["daandesc"];
   	 	$caiji_best= $_REQUEST["daanbest"];
   	 	$caiji_hdusertx= $_REQUEST["caiji_hdusertx"];
   	 	$caiji_hdusername= $_REQUEST["caiji_hdusername"];
    	 		$bianma=$_REQUEST["bianma"];
    	 		$ckabox=$_REQUEST["ckabox"];
    	 		$imgckabox=$_REQUEST["imgckabox"];
    	 	 $html = file_get_html($caiji_url);
// 
$res="";
    	 	 try{
    	 	 	 
    	 	 $desc='';
    	 	 if($caiji_desc!=''){
    	 	 $wtdesc= $html->find($caiji_desc);
    
    	 	$desc= $wtdesc[0]->outertext ;
    	 	  $suffer= substr($desc, 0,4);
    	 	if($suffer=='<pre'){
    	 		$desc=$wtdesc[0]->plaintext   ;
    	 	}
    	 	
    	 	 }
    	 	 
    	 	 if($title==""||$title==null){
    	 	 	
    	 	 	 return false ; 
    	 	 }
    	 	$q= $_ENV['question']->get_by_title(htmlspecialchars($title));
    	 	 if($q!=null)
    	 	 return false ; 
    	 	 
    	 	// $desc=htmlspecialchars($desc);
    	 	 try{
                require_once ASK2_STATIC_ROOT.'/js/neweditor/php/Config.php';
                if(Config::OPEN_OSS){
          	 require_once ASK2_STATIC_ROOT.'/js/neweditor/php/up.php';
    
				$desc=str_replace('<img class=">ͼ"', '', $desc);
					 $desc= str_replace('<img class=">图" class="ikqb_img_alink', '', $desc);
    	 	   	 	 
    	 	   	 
    	 	   	  
    	 	                 
            if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($desc);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$desc=str_replace($img_arr[1][$i],$img_url, $desc);
}
    	 	   	 	 }	

}
                }
                 }catch (Exception $e){
                	print $e->getMessage();  
                }
    	 	   	 if($ckabox=='true'||$ckabox=='on'){
	
	$desc=preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $desc);
         }
    	 	 if($imgckabox=='true'||$imgckabox=='on'){
	  
	   $desc=preg_replace('/<img[^>]+>/i','',$desc);
}   	 	
  	 $desc=str_replace("'", '"', $desc);
    	 	 $qid = $_ENV['question']->add_seo(htmlspecialchars($title),$uid,$username,$desc, 0, rand(0, 100), $cid, $cid1, $cid2, $cid3, 1,rand(10, 200),$mtime);

    	 	 $numuser=rand(3, 5);
    	 	 for($i=0;$i<=$numuser;$i++){
    	 	 	 $auid=array_rand($userlist,1);
    	 $_uid=$userlist[$auid]['uid'];
    	 $_username=$userlist[$auid]['username'];
    	 	 	 $this->attention_question($qid,$_uid,$_username);
    	 	 }
    	 	
    
        
        $taglist=dz_segment(htmlspecialchars($title));
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
    	 	 
    	 	 $_ENV['doing']->add($uid, $username, 1, $qid, '');
    	 	 }catch (Exception $err){
    	 	 	$res='dddd';//$err->getMessage();
    	 	 	//print $err->getMessage(); 
    	 	 }
    	 	 $wtbest= $html->find($caiji_best);
    	 $atest= $wtbest[0]->outertext ;
$type_fill = $html->find($caiji_prefix);

if(isset($wtbest)&&$wtbest!=""&&$wtbest!=null)
$type_fill=array_merge($wtbest,$type_fill);
//
////print_r($type_fill);

$caijilist=array();
$count1=0;
$commentarr=array('真给力',"谢谢你",'非常感谢你','你真是个大好人','你真的帮了我大忙','高手留个联系方式吧','大神......');
$comment=$commentarr[array_rand($commentarr,1)];
 $countarr=count($type_fill);
 $rand=rand(1, $countarr);
 $num=1;
$aid=0;
$answer_adopt_num=rand(1,10)%2;
foreach ($type_fill as $r) {
	
	  $randtime=rand(1,$huidatime);
 $t2=date('Y-m-d H:i:s',strtotime("-$randtime minute"));
 


            $mtime=strtotime( $t2);
	$caijilist[$count1]['num']=$count1;
	
	if($bianma=='gb2312'){
  $caijilist[$count1]['title']=iconv('gb2312', 'utf-8',$r->outertext)  ;
	}else{
			$str= $r->outertext  ;
		$str=str_replace("'", "", $str);
		$str=str_replace('<pre style="font-family:微软雅黑;">', "", $str);
				$str=str_replace('</pre>', "", $str);
		$caijilist[$count1]['title']=$str ;
		//$caijilist[$count1]['title']= $r->outertext  ;
	}
	if(strstr($caijilist[$count1]['title'],'<span>热心卡友</span>')){
		continue;
	}
	if(count($userlist)>0){
	$quid=array_rand($userlist,1);
	$wdusername=	$html->find($caiji_hdusername);
    	 $wendausername=$wdusername[$count1]->plaintext;
    	 $wduserimage=$html->find($caiji_hdusertx);
    	 
    	 $wendauserimg=$wduserimage[$count1]->src;
	
	//print_r($wendauserimg) ;
	$user = $_ENV['user']->get_by_username($wendausername);
	
    	 	 	 if(!$user){
    	 	 	 	$hduid = $_ENV['user']->caijiadd($wendausername, '123456', rand(1111111, 99999999)."@qq.com");
    	 	 	 
    	 	 	 	
    	 	 	 	 $hduid = intval($hduid);
            $avatardir = "/data/avatar/";
             $extname = substr(strrchr($wendauserimg,'.'), 1);
             $upload_tmp_file = ASK2_ROOT . '/data/tmp/user_avatar_' . $hduid . '.' . $extname;
            $hduid = abs($hduid);
            $hduid = sprintf("%09d", $hduid);
            $dir1 = $avatardir . substr($hduid, 0, 3);
            $dir2 = $dir1 . '/' . substr($hduid, 3, 2);
            $dir3 = $dir2 . '/' . substr($hduid, 5, 2);
            (!is_dir(ASK2_ROOT . $dir1)) && forcemkdir(ASK2_ROOT . $dir1);
            (!is_dir(ASK2_ROOT . $dir2)) && forcemkdir(ASK2_ROOT . $dir2);
            (!is_dir(ASK2_ROOT . $dir3)) && forcemkdir(ASK2_ROOT . $dir3);
            
            $smallimg = $dir3 . "/small_" . $hduid . '.' . $extname;
             $smallimgdir = $dir3."/" ;
    	 	 	 	$this->getImage($wendauserimg,"small_" . $hduid . '.' . $extname, ASK2_ROOT . $smallimgdir, array('jpg','jpeg','png', 'gif'));
    	 	 	 }else{
    	 	 	 	$hduid=$user['uid'];
    	 	 	 }
	if($wendausername==''){
		$hduid=$userlist[$quid]['uid'];
		$wendausername=$userlist[$quid]['username'];
	}
	
	$answer_content=$caijilist[$count1]['title'];
	
	
//	  	 	 $img_arr=getfirstimgs($answer_content);
//	  	 	 if($img_arr[1]!=null){
//    	 	 for($i=0;$i<count($img_arr[1]);$i++){
//	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","upload/",1);
//	$answer_content=str_replace($img_arr[1][$i],SITE_URL.$img_url, $answer_content);
//}
//	  	 	 }

	    	 	   	 	 
    	 	   	 	
	  if(Config::OPEN_OSS){ 	  
    	 	                 
            if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($answer_content);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$answer_content=str_replace($img_arr[1][$i],$img_url, $answer_content);
}
    	 	   	 	 }	

}
	  }
 
	 	 
 if($ckabox=='true'||$ckabox=='on'){
	
	$answer_content=preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $answer_content);
         }
    	 	 if($imgckabox=='true'||$imgckabox=='on'){
	  
	   $answer_content=preg_replace('/<img[^>]+>/i','',$answer_content);
}   	 	
  	 	$answer_content=str_replace("'", '"', $answer_content);
 $aid= $_ENV['answer']->add_seo($qid,$title,$answer_content,$hduid,$wendausername,1,rand(20, 80),$mtime);
  unset($userlist[array_search($wendausername,$userlist)]); 
//$_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	   // $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid, $userlist[$quid]['uid'], $caijilist[$count1]['title']);
 
  if($answer_adopt_num==0){
  $answer = $_ENV['answer']->get($aid);
 
 if($countarr==1){
      	
      	 $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
        $_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	    $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid,$hduid, $caijilist[$count1]['title']);
        }
        }else{
        	if($rand==$num){
        		 $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
        		$_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	    $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid, $hduid, $caijilist[$count1]['title']);
        	}
        	}
        }
  }
        }else{
        	
        $answer_content=$caijilist[$count1]['title'];
	
	
//	  	 	 $img_arr=getfirstimgs($answer_content);
//	  	 	 if($img_arr[1]!=null){
//    	 	 for($i=0;$i<count($img_arr[1]);$i++){
//	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","upload/",1);
//	$answer_content=str_replace($img_arr[1][$i],SITE_URL.$img_url, $answer_content);
//}
//	  	 	 }

         if($ckabox=='true'||$ckabox=='on'){
	
	$answer_content=preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $answer_content);
         }
    	 	 if($imgckabox=='true'||$imgckabox=='on'){
	  
	   $answer_content=preg_replace('/<img[^>]+>/i','',$answer_content);
	   
}   	     	 	                 
            if(Common::getOpenoss()=='1'){
            	$img_arr=getfirstimgs($answer_content);
            		 if($img_arr[1]!=null){
    	 	 for($i=0;$i<count($img_arr[1]);$i++){
	$img_url=getImageFile($img_arr[1][$i],rand(100000, 99999999).".jpg","data/upload/",1);
    	 	 $diross=$img_url;
$tmpfile=$img_url;

if(substr($img_url, 0,1)=='/'){
	$diross=substr($img_url, 1);
}
$img_url=uploadFile(Common::getOssClient(), Common::getBucketName(),$diross,ASK2_ROOT .'/'. $img_url);
if($img_url!='error'){
	unlink(ASK2_ROOT .'/'. $tmpfile);
}
	//$desc=str_replace($img_arr[1][$i],SITE_URL.$img_url, $desc);
	$answer_content=str_replace($img_arr[1][$i],$img_url, $answer_content);
}
    	 	   	 	 }	

}
$answer_content=str_replace("'", '"', $answer_content);
		$aid= $_ENV['answer']->add_seo($qid,$title,$answer_content,0,'游客',1,rand(20, 80),$mtime);
		  if($answer_adopt_num==0){
		$answer = $_ENV['answer']->get($aid);
		 if($countarr==1){
		 	 $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
		$_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	    $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid, 0, $caijilist[$count1]['title']);
        }
        }else{
		
        	if($rand==$num){
        		 $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
        		$_ENV['answer_comment']->add($aid, $comment, $uid, $username);
	    $_ENV['doing']->add($uid, $username, 8, $qid, $comment, $aid, $userlist[$quid]['uid'], $caijilist[$count1]['title']);
        	}
        	}
		 }
		  }
	}
 $count1++;
 $num++;
}

   	 $html->clear();
   	 //echo ASK2_ROOT.$smallimg;
   //	print_r($wduserimage);
   
    	
   echo 'success:'.$title;
   	// $tt=array_rand($userlist,1);
  // 	 print_r($userlist[$tt]['username']);
 // echo $desc;
    //	echo count($type_fill);
   // echo $count1;
    }
    

     /* 采集数据 */

    function oncaiji() {
    	 $page = max(1, intval($this->get[2]));
    	$pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $userlist = $_ENV['user']->get_caiji_list($startindex, $pagesize);
         $categoryjs = $_ENV['category']->get_js();
      //    $catetree = $_ENV['category']->get_categrory_tree($_ENV['category']->get_list());
    	 if (isset($this->post['submit'])) {
    	 	require 'simple_html_dom.php';
    	 	//include 'lib/simple_html_dom.php';
    	 	$caiji_url= $this->post['caiji_url'];
    	 
    	 	$ckbox= $this->post['ckbox'];
    	 	
    	 	//$srchcategory= $this->post['srchcategory'];
    	 	$s_username= $this->post['s_username'];
    	 	$caiji_prefix= $this->post['caiji_prefix'];
    	 	$caiji_daan= $this->post['caiji_daan'];
    	 	$caiji_yuming= $this->post['caiji_yuming'];
    	 	$caiji_desc= $this->post["caiji_desc"];
    	 	$caiji_best= $this->post["caiji_best"];
    	 	$caiji_hdusertx= $this->post["caiji_hdusertx"];
    	 	$caiji_beginnum= $this->post["caiji_beginnum"];
    	 	$caiji_endnum= $this->post["caiji_endnum"];
    	 	$caiji_hdusername= $this->post["caiji_hdusername"];
    	 	
    	 	$bianma=$this->post['bianma'];
    	 	session_start();
    	 	$c_fenlei=$this->post['category1'];
    	    
    	 $html = file_get_html($caiji_url);
 

$type_fill = $html->find($caiji_prefix);
//echo $type_fill[0]->plaintext;

//echo count($type_fill);
$caijilist=array();
$count1=0;
foreach ($type_fill as $r) {
	//echo  $r->plaintext ;
	//break;
	$caijilist[$count1]['num']=$count1;
	if($bianma=='gb2312'){
  $caijilist[$count1]['title']=iconv('gb2312', 'utf-8',$ckbox!='on'? $r->plaintext :$r->title)  ;
	}else{
		$caijilist[$count1]['title']= $ckbox!='on'? $r->plaintext :$r->title  ;
	}
 $caijilist[$count1]['href']= $r->href ;
   $count1++;
}
if(count($caijilist)==0) {
	 $message = '没有匹配的结果！';
}
   	 $html->clear();
    	 }
    	
     include ASK2_ROOT.'/static/caiji/xml.php';
     $ul_li='  ';
       if($urllist){
       
       	foreach($urllist as $key=>$val){
       		 $ul_li.='    <li class="nav-parent">';
       			$ul_li .=' <a href="javascript:;">
            <i class="icon-list-ul"></i>'.
                       $key.
             '<i class="icon-chevron-right nav-parent-fold-icon"></i>
          
             </a> <ul class="nav" style="display: none;">';
                       foreach ($val as $k=>$v){
                       	 $ul_li .='  <li class="liset" path="'.$v.'"><a href="javascript:;">'.$k.'</a></li>';
                       }
                       $ul_li .=' </ul>';
                         $ul_li.=' </li>';
       	}
       }
     
     
    	 include template('setting_caiji', 'admin');
    	
    }
    function onajaxpostpage(){
    		require 'simple_html_dom.php';
    		//include 'lib/simple_html_dom.php';
    	
    	$caiji_url=$_REQUEST["caiji_url"];
    	$caiji_prefix=$_REQUEST["caiji_prefix"];
    	    $bianma=$_REQUEST['bianma'];
    	    $ckbox=$_REQUEST['ckbox'];
    	 $html = file_get_html($caiji_url);
       

$type_fill = $html->find($caiji_prefix);
//echo $type_fill[0]->plaintext;

//echo count($type_fill);
$caijilist=array();
$count1=0;

foreach ($type_fill as $r) {
	//echo  $r->plaintext ;
	//break;
	$caijilist[$count1]['num']=$count1;
	if($bianma=='gb2312'){
  $caijilist[$count1]['title']=iconv('gb2312', 'utf-8',$ckbox!='true'? $r->plaintext :$r->title)  ;
	}else{
		$caijilist[$count1]['title']= $ckbox!='true'? $r->plaintext :$r->title  ;
	}
 $caijilist[$count1]['href']= $r->href ;
   $count1++;
}
if(count($caijilist)==0) {
	 $caijilist =null;
}
   	 $html->clear();
    	echo json_encode($caijilist);
    }
    
  
function getImage($url, $filename='', $dirName, $fileType, $type=0)
{
    if($url == ''){return false;}
    //获取文件原文件名
    $defaultFileName = basename($url);
    //获取文件类型
    $suffix = substr(strrchr($url,'.'), 1);
    if(!in_array($suffix, $fileType)){
        return false;
    }
    //设置保存后的文件名
  //  $filename = $filename == '' ? time().rand(0,9).'.'.$suffix : $defaultFileName;
          
    //获取远程文件资源
    if($type){
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file = curl_exec($ch);
        curl_close($ch);
    }else{
        ob_start();
        readfile($url);
        $file = ob_get_contents();
        ob_end_clean();
    }
    //设置文件保存路径
   // $dirName = $dirName.'/'.date('Y', time()).'/'.date('m', time()).'/'.date('d',time()).'/';
    if(!file_exists($dirName)){
        mkdir($dirName, 0777, true);
    }
    //保存文件
    $res = fopen($dirName.$filename,'a');
    fwrite($res,$file);
    fclose($res);
    return "{'fileName':$filename, 'saveDir':$dirName}";
}
    /* 注册设置 */

    function onregister() {
        if (isset($this->post['submit'])) {
            $this->setting['allow_register'] = $this->post['allow_register'];
             $this->setting['register_on'] = $this->post['register_on'];
            $this->setting['max_register_num'] = $this->post['max_register_num'];
            $this->setting['access_email'] = $this->post['access_email'];
            $this->setting['censor_email'] = $this->post['censor_email'];
            $this->setting['censor_username'] = $this->post['censor_username'];
            $_ENV['setting']->update($this->setting);
            $message = '注册设置更新成功！';
        }
        include template('setting_register', 'admin');
    }

    /* 邮件设置 */

    function onmail() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('mail' == substr($key, 0, 4)) {
                    $this->setting[$key] = $value;
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '邮件设置更新成功！';
        }
        include template('setting_mail', 'admin');
    }
    /* 测试发送邮件 */

    function ontestmail() {
        if (isset($this->post['submit'])) {
             $toemail=$this->post['toemail'];
             
             $subject=$this->post['subject'];
              $message=$this->post['message'];
              $tousername=$this->post['tousername'];
          $state=  sendmailto($toemail, $subject, $message,$tousername);
        if($state==""){
		 $message = "对不起，邮件发送失败！请检查邮箱填写是否有误。";
		
		
	}else{
		 $message = '邮件测试成功！';
	}
           
        }else{
        	  $message = '没有提交操作！';
        }
        include template('setting_mail', 'admin');
    }
    


    /* 积分设置 */

    function oncredit() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('credit' == substr($key, 0, 6)) {
                    $this->setting[$key] = $value;
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '积分设置更新成功！';
        }
        include template('setting_credit', 'admin');
    }

    /* 缓存设置 */

    function oncache() {
        $tplchecked = $datachecked = false;
        if (isset($this->post['submit'])) {
            if (isset($this->post['type'])) {
                if (in_array('tpl', $this->post['type'])) {
                    $tplchecked = true;
                    cleardir(ASK2_ROOT . '/data/view');
                }
                if (in_array('data', $this->post['type'])) {
                    $datachecked = true;
                    cleardir(ASK2_ROOT . '/data/cache');
                }
                $message = '缓存更新成功！';
            } else {
                $tplchecked = $datachecked = false;
                $message = '没有选择缓存类型！';
                $type = 'errormsg';
            }
        }
        include template('setting_cache', 'admin');
    }

    /* 通行证设置 */

    function onpassport() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('passport' == substr($key, 0, 8)) {
                    $this->setting[$key] = $value;
                }
            }
            $this->setting['passport_credit1'] = intval(isset($this->post['passport_credit1']));
            $this->setting['passport_credit2'] = intval(isset($this->post['passport_credit2']));
            $_ENV['setting']->update($this->setting);
            $message = '通行证设置更新成功！';
        }
        include template('setting_passport', 'admin');
    }

    /* UCenter设置 */

    function onucenter() {
        if (isset($this->post['submit'])) {
        	
            $this->setting['ucenter_open'] = intval($this->post['ucenter_open']);
            $this->setting['ucenter_url']=strip_tags($this->post['ucenter_url']);
            
         
            $_ENV['setting']->update($this->setting);
            if ($this->post['ucenter_config']&&trim($this->post['ucenter_config'])!=''){
                $ucconfig = "<?php\n";
                $ucconfig.=tstripslashes($this->post['ucenter_config']);
                writetofile(ASK2_ROOT . '/data/ucconfig.inc.php',$ucconfig);
            }
            //连接ucenter服务端，生成uc配置文件
            $message = 'UCenter设置完成！';
            
           
        }
        include template('setting_ucenter', 'admin');
    }

 
    /* SEO设置 */

    function onseo() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('seo' == substr($key, 0, 3)) {
                    $this->setting[$key] = $value;
                }
            }
            $this->setting['baidu_api'] = $this->post['baidu_api'];
            $this->setting['seo_prefix'] = ($this->post['seo_on']) ? '' : '?';
            $_ENV['setting']->update($this->setting);
            $message = 'SEO设置更新成功！';
        }
        include template('setting_seo', 'admin');
    }

    /* 消息模板 */

    function onmsgtpl() {
        if (isset($this->post['submit'])) {
            $msgtpl = array();
            for ($i = 1; $i <= 4; $i++) {
                $message['title'] = $this->post['title' . $i];
                $message['content'] = $this->post['content' . $i];
                $msgtpl[] = $message;
            }
            $this->setting['msgtpl'] = serialize($msgtpl);
            $_ENV['setting']->update($this->setting);
            unset($type);
            $message = '消息模板设置成功!';
        }
        $msgtpl = unserialize($this->setting['msgtpl']);
        include template('setting_msgtpl', 'admin');
    }

    /* 生成htm页面 */

    function onhtm() {
        $minqid = $this->get[2];
        $maxqid = $this->get[3];
        $qid = $this->get[4];
        $this->load('question');
        $question = $_ENV['question']->get($qid);
        if ($question && 0 != $question['status'] && 9 != $question['status']) {
            $this->write_question($question);
        }
        $nextqid = $qid + 1;
        $finish = $qid - $minqid + 1;
        include template('makehtm', 'admin');
    }

    /* 防采集设置 */

    function onstopcopy() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('stopcopy' == substr($key, 0, 8)) {
                    $this->setting[$key] = strtolower($value);
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '防采集设置更新成功！';
        }
        include template('setting_stopcopy', 'admin');
    }

    /* 更新问答统计 */

    function oncounter() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('counter' == substr($key, 0, 7)) {
                    $this->setting[$key] = strtolower($value);
                }
            }
            $_ENV['setting']->update_counter();
            $_ENV['setting']->update($this->setting);
            $message = '问答统计更新成功！';
        }
        include template('setting_counter', 'admin');
    }

    /*     * 广告管理* */

    function onad() {
        if (isset($this->post['submit'])) {
            $this->setting['ads'] = taddslashes(serialize($this->post['ad']), 1);
            $_ENV['setting']->update($this->setting);
            $type = 'correctmsg';
            $message = '广告修改成功!';
            $this->setting = $this->cache->load('setting');
        }
        $adlist = tstripslashes(unserialize($this->setting['ads']));
        include template('setting_ad', 'admin');
    }

    /**
     * 搜索设置
     */
    function onsearch() {
        if (isset($this->post['submit'])) {
            $this->setting['search_placeholder'] = $this->post['search_placeholder'];
             $this->setting['search_shownum'] = $this->post['search_shownum'];
            $this->setting['xunsearch_open'] = $this->post['xunsearch_open'];
            $this->setting['xunsearch_sdk_file'] = $this->post['xunsearch_sdk_file'];
            if ($this->setting['xunsearch_open'] && !file_exists($this->setting['xunsearch_sdk_file'])) {
                $type = 'errormsg';
                $message = 'SDK文件不存在，请核实!';
            } else {
                $type = 'correctmsg';
                $message = '搜索设置成功!';
            }
            $_ENV['setting']->update($this->setting);
        }
        include template('setting_search', 'admin');
    }

    /**
     * 生产全文检索
     */
    function onmakewords() {
        $this->load("question");
        $_ENV['question']->make_words();
    }

    /* qq互联设置 */

    function onqqlogin() {
        if (isset($this->post['submit'])) {
            $this->setting['qqlogin_open'] = $this->post['qqlogin_open'];
            $this->setting['qqlogin_appid'] = trim($this->post['qqlogin_appid']);
            $this->setting['qqlogin_key'] = trim($this->post['qqlogin_key']);
            $this->setting['qqlogin_avatar'] = trim($this->post['qqlogin_avatar']);
            $_ENV['setting']->update($this->setting);
            $this->setting = $this->cache->load('setting');
            $logininc = array();
            $logininc['appid'] = $this->setting['qqlogin_appid'];
            $logininc['appkey'] = $this->setting['qqlogin_key'];
            $logininc['callback'] = SITE_URL . 'plugin/qqlogin/callback.php';
            $logininc['scope'] = "get_user_info";
            $logininc['errorReport'] = "true";
            $logininc['storageType'] = "file";
            $loginincstr = "<?php die('forbidden'); ?>\n" . json_encode($logininc);
            $loginincstr = str_replace("\\", "", $loginincstr);
            writetofile(ASK2_ROOT . "/plugin/qqlogin/API/comm/inc.php", $loginincstr);
            $message = 'qq互联参数保存成功！';
        }
        include template("setting_qqlogin", "admin");
    }

    /* sina互联设置 */

    function onsinalogin() {
        if (isset($this->post['submit'])) {
            $this->setting['sinalogin_open'] = $this->post['sinalogin_open'];
            $this->setting['sinalogin_appid'] = trim($this->post['sinalogin_appid']);
            $this->setting['sinalogin_key'] = trim($this->post['sinalogin_key']);
            $this->setting['sinalogin_avatar'] = trim($this->post['sinalogin_avatar']);
            $_ENV['setting']->update($this->setting);
            $this->setting = $this->cache->load('setting');
            $config = "<?php \r\ndefine('WB_AKEY',  '" . $this->setting['sinalogin_appid'] . "');\r\n";
            $config .= "define('WB_SKEY',  '" . $this->setting['sinalogin_key'] . "');\r\n";
            $config .= "define('WB_CALLBACK_URL',  '" . SITE_URL . 'plugin/sinalogin/callback.php' . "');\r\n";
            writetofile(ASK2_ROOT . '/plugin/sinalogin/config.php', $config);
            $message = 'sina互联参数保存成功！';
        }
        include template("setting_sinalogin", "admin");
    }

    /* 财富充值设置 */

    function onebank() {
        if (isset($this->post['submit'])) {
            $aliapy_config = array();
            $this->setting['recharge_open'] = $this->post['recharge_open'];
            $this->setting['recharge_rate'] = trim($this->post['recharge_rate']);
            $aliapy_config['seller_email'] = $this->setting['alipay_seller_email'] = $this->post['alipay_seller_email'];
            $aliapy_config['partner'] = $this->setting['alipay_partner'] = trim($this->post['alipay_partner']);
            $aliapy_config['key'] = $this->setting['alipay_key'] = trim($this->post['alipay_key']);
            $aliapy_config['sign_type'] = 'MD5';
            $aliapy_config['input_charset'] = strtolower(ASK2_CHARSET);
            $aliapy_config['transport'] = 'http';
            $aliapy_config['return_url'] = SITE_URL . "index.php?ebank/aliapyback";
            $aliapy_config['notify_url'] = "";
            $_ENV['setting']->update($this->setting);
            $strdata = "<?php\nreturn " . var_export($aliapy_config, true) . ";\n?>";
            writetofile(ASK2_ROOT . "/data/alipay.config.php", $strdata);
        }
        include template("setting_ebank", "admin");
    }
function ongetfolders() {
 	$file_dir="static/caiji";
 	 $shili = $file_dir ; 
       if ( !file_exists ( $shili )){ 
          echo $shili."目录不存在!" ; 
      }else{   
       $i = 0; 
       $file='';
         if ( is_dir ( $shili )){                   //检测是否是合法目录 
           if ($shi = opendir ( $shili )){          //打开目录 
              while ($li = readdir( $shi )){       //读取目录 
                  $i++ ; 
                  $temps=explode('.', $li);
                 $file=$file.$temps[0].',';
                } } }     //输出目录中的内容 
                 echo trim($file,",") ; 
         closedir ( $shi ) ;  } 
 }
}

?>