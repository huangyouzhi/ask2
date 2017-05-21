<?php

!defined('IN_ASK2') && exit('Access Denied');

class userbankmodel {

    var $db;
    var $base;

    function userbankmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    function getlistbytouid($touid,$start,$limit){
    	$recargelist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "userbank where touid=$touid ORDER BY time DESC LIMIT $start,$limit");
        while ($money = $this->db->fetch_array($query)) {
        	$fromuser=$this->getuser($money['fromuid']);
        	
            $money['fromusername'] =$fromuser['username'];
             $money['format_time']=tdate($money['time']);
            
            $recargelist[] = $money;
        }
        return $recargelist;
    }
    function getuser($uid){
    	  $user = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='$uid'");
    	  return $user;
    }
    function getsummoneybytouid($touid){
    	
    	$mrmb = $this->db->fetch_first("SELECT sum(money) as rmb FROM " . DB_TABLEPRE . "userbank WHERE touid=$touid ");
        return $mrmb;
    	
    }
    
    function getmymoney($touid,$start,$limit){
    	$recargelist = array();
        $query = $this->db->query("SELECT DISTINCT `transaction_id`,`time_end`,`openid`,`out_trade_no`,`cash_fee`, `type`,`typeid`,`touid`,`haspay`,`trade_type`,`trade_state` FROM " . DB_TABLEPRE . "weixin_notify where touid=$touid  ORDER BY time_end DESC ,haspay desc LIMIT $start,$limit");
     $suffix='?';
        if( $this->base->setting['seo_on']){
        	$suffix='';
        }
        while ($money = $this->db->fetch_array($query)) {
        	//$fromuser=$this->getuser($money['touid']);
        	 $money['cash_fee'] = $money['cash_fee']/100;
        	  if( $money['haspay']==0){
        	  	$money['msg']="可提现";
        	  }else{
        	  	$money['msg']="已经提现";
        	  }
           // $money['fromusername'] =$fromuser['username'];
             $money['format_time']=tdate($money['time_end']);
             switch ($money['type']){
             	case 'viewaid':
             			 $money['operation']='用户付费偷看';
             		
             		  $mod=$this->getanswer($money['typeid']);
             		 
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="偷看回答的问题:<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  
             		  
             		break;
             			 	case 'myviewaid':
             			 $money['operation']='我的偷看回答';
             		
             		  $mod=$this->getanswer($money['typeid']);
             		 
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="付费偷看回答的问题:<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  
             		  
             		break;
             		case 'chongzhi':
             		 $money['operation']='用户充值';
             		
             		 
             		  
             		   $money['content']="来自用户充值付款";
             		break;
             	case 'aid':
             		 $money['operation']='回答打赏';
             		 $mod=$this->getanswer($money['typeid']);
             		 
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		break;
             			case 'tid':
             		 $money['operation']='文章打赏';
             		  $mod=$this->gettopic($money['typeid']);
             		 $viewurl =SITE_URL.$suffix. urlmap('topic/getone/' . $mod['id'], 2);
             		   $money['content']="<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		 
             		break;
             		case 'qid':
             		 $money['operation']='提问悬赏';
             		break;
             }
           
            $recargelist[] = $money;
        }
        return $recargelist;
    }
 function getzhangdan($touid,$start,$limit){
    	$recargelist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "paylog where touid=$touid  ORDER BY time DESC  LIMIT $start,$limit");
     $suffix='?';
        if( $this->base->setting['seo_on']){
        	$suffix='';
        }
        while ($money = $this->db->fetch_array($query)) {
        	//$fromuser=$this->getuser($money['touid']);
        	// $money['cash_fee'] = $money['money']/100;
        	
           // $money['fromusername'] =$fromuser['username'];
             $money['time']=tdate($money['time']);
             switch ($money['type']){
             	 	case 'viewaid':
             			 $money['operation']='用户付费偷看';
             		$money['money']="收入".$money['money']."元";
             		  $mod=$this->getanswer($money['typeid']);
             		 
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="偷看回答的问题:<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  
             		  
             		break;
             		 	case 'myviewaid':
             			 $money['operation']='我的偷看回答';
             		$money['money']="支出".$money['money']."元";
             		  $mod=$this->getanswer($money['typeid']);
             		 
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="付费偷看回答的问题:<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  
             		  
             		break;
             		case 'chongzhi':
             		 $money['operation']='用户充值';
             		
             		 $money['money']="收入".$money['money']."元";
             		  
             		   $money['content']="来自用户充值付款";
             		break;
             	case 'aid':
             		 $money['operation']='回答打赏';
             		 $mod=$this->getanswer($money['typeid']);
             		  $money['money']="收入".$money['money']."元";
             		  $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['qid'], 2);
             		   $money['content']="<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		break;
             			case 'tid':
             		 $money['operation']='文章打赏';
             		  $mod=$this->gettopic($money['typeid']);
             		 $viewurl =SITE_URL.$suffix. urlmap('topic/getone/' . $mod['id'], 2);
             		   $money['content']="<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		 
             		break;
             		case 'wtxuanshang':
             		 $money['operation']='提问悬赏';
             		  $mod=$this->getquestion($money['typeid']);
             $money['money']="支出".$money['money']."元";
             		  if( $mod==null){
             		  	$money['content']="此悬赏问题被删除，问题qid=".$money['typeid'];
             		  }else{
             		  	 $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['id'], 2);
             		   $money['content']="悬赏标题-><a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  }
             		break;
             			case 'adoptqid':
             		 $money['operation']='回答被采纳';
             		  $money['money']="收入".$money['money']."元";
             		  $mod=$this->getquestion($money['typeid']);
             		    $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['id'], 2);
             		   $money['content']="<a href='".$viewurl.".html'>".$mod['title']."</a>";
             		break;
             			case 'thqid':
             		 $money['operation']='问题被删除退还悬赏金额';
             		 $money['money']="收入".$money['money']."元";
             		
             		   $money['content']="此删除问题qid=".$money['typeid'];
             		break;
             			case 'theqid':
             		 $money['operation']='退还对专家付费提问金额';
             		  $money['money']="收入".$money['money']."元";
             		 $mod=$this->getquestion($money['typeid']);
             		  if( $mod==null){
             		  	$money['content']="此问题被删除，问题qid=".$money['typeid'];
             		  }else{
             		  	 $viewurl =SITE_URL. $suffix.urlmap('question/view/' . $mod['id'], 2);
             		   $money['content']="标题-><a href='".$viewurl.".html'>".$mod['title']."</a>";
             		  }
             		   
             		break;
             }
           
            $recargelist[] = $money;
        }
        return $recargelist;
    }
  function getanswer($id) {
        $answer= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "answer WHERE id='$id'");
        
         if ($answer) {
          
             $answer['title']=checkwordsglobal( $answer['title']);
              $answer['content']=checkwordsglobal( $answer['content']);
        }
        return $answer;
    }
   function getmysummoneybytouid($touid){
    	
    	$mrmb = $this->db->fetch_first("SELECT sum(cash_fee) as rmb FROM " . DB_TABLEPRE . "weixin_notify WHERE touid=$touid and haspay=0 ");
        //$mrmb=intval($mrmb)/100;
    	return $mrmb;
    	
    }
   function gethasmysummoneybytouid($touid){
    	
    	$mrmb = $this->db->fetch_first("SELECT sum(cash_fee) as rmb FROM " . DB_TABLEPRE . "weixin_notify WHERE touid=$touid and haspay=1 ");
        //$mrmb=intval($mrmb)/100;
    	return $mrmb;
    	
    }
    function getquestion($id) {
        $question = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "question WHERE id='$id'");
        if ($question) {
          
             $question['title']=checkwordsglobal( $question['title']);
             
        }
        return $question;
    }
   function gettopic($id) {
         $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE id='$id'");
        
        if ($topic) {
         
            $topic['title'] = checkwordsglobal($topic['title']);
              
        }
        return $topic;
    }
}