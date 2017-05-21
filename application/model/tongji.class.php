<?php

!defined('IN_ASK2') && exit('Access Denied');

class tongjimodel {

    var $db;
    var $base;

    function tongjimodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

  //获取今日注册用户数
    function rownum_by_today_user_regtime($starttime,$endtime) {
    	
    	return $this->db->fetch_total('user', "regtime>=$starttime and regtime<=$endtime ");
    	

    }


  //获取今日问题数
    function rownum_by_today_submit_question($starttime,$endtime) {
    
        
        return $this->db->fetch_total('question', "time>=$starttime and time<=$endtime  ");
    	
    }
  //获取今日问题数
    function rownum_by_today_submit_answer($starttime,$endtime) {

              return $this->db->fetch_total('answer', " time>=$starttime and time<=$endtime ");
    }


}
?>
