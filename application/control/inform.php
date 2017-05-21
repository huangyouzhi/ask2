<?php
!defined('IN_ASK2') && exit('Access Denied');

class informcontrol extends base {

    function informcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load("inform");
    }

    /*添加举报*/
    function onadd() {
    	
        empty($this->post['group-type']) && $this->message('请选择举报类型，谢谢！','BACK');
         empty($this->post['type']) && $this->message('请选择举报原因，谢谢！','BACK');
           empty($this->post['content']) && $this->message('请填写举报描述，谢谢！','BACK');
//        $inform = $_ENV['inform']->get($this->post['qid']);
//        if($inform) {
//            $contents =  unserialize($inform['content']);
//            $infrom_keywords = unserialize($inform['keywords']);
//            (!in_array($this->post['content'],$contents)) && $contents[]=$this->post['content'];
//            $newwords = array_unique(array_merge($this->post['informkind'],$infrom_keywords));
//            $_ENV['inform']->update($this->post['title'],serialize($contents),serialize($newwords),$this->post['qid']);
//        }else {
//add($qid,$qtitle,$uid,$username,$aid,$title,$content,$keywords)
            $_ENV['inform']->add($this->post['qid'],$this->post['qtitle'],$this->user['uid'],$this->user['username'],$this->post['aid'],$this->post['type'],$this->post['content'],$this->post['group-type']);
        //}
        $this->message('举报成功，健康的网络环境需要大家共同维护，谢谢您的支持 :)','BACK');
    }
}
?>