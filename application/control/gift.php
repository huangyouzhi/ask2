<?php

!defined('IN_ASK2') && exit('Access Denied');

class giftcontrol extends base {

    function giftcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('gift');
        $this->load('user');
    }

    function ondefault() {
    	$navtitle = "礼品商店";
        @$page = max(1, intval($this->get[2]));
        $pagesize= 12;
        $startindex = ($page - 1) * $pagesize;
        $giftlist = $_ENV['gift']->get_list($startindex,$pagesize);
        $giftnum=$this->db->fetch_total('gift');
        $departstr=page($giftnum, $pagesize, $page,"gift/default");
        $loglist = $_ENV['gift']->getloglist(0, 30);
        include template('giftlist');
    }
    
    function onsearch() {
        $from = intval($this->get[2]);
        $to = intval($this->get[3]);
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $giftlist = $_ENV['gift']->get_by_range($from,$to,$startindex,$pagesize);
        $rownum=$this->db->fetch_total('gift'," `credit`>=$from AND `credit`<=$to");
        $departstr=page($rownum, $pagesize, $page,"gift/search/$from/$to");
        $ranglist = unserialize($this->setting['gift_range']);
        include template('giftlist');
    }

    function onadd() {
        if(isset($this->post['realname'])) {
            $realname =strip_tags( $this->post['realname']);
            $email = strip_tags( $this->post['email']);
            $phone =strip_tags(  $this->post['phone']);
            $addr =strip_tags(  $this->post['addr']);
            $postcode =strip_tags( $this->post['postcode']);
            $qq =strip_tags(  $this->post['qq']);
            $notes =strip_tags(  $this->post['notes']);
            $gid =strip_tags(  $this->post['gid']);
            $param = array();
            if(''==$realname || ''==$email || ''==$phone||''==$addr||''==$postcode) {
                $this->message("为了准确联系到您，真实姓名、邮箱、联系地址（邮编）、电话不能为空！",'gift/default');
            }

            if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/",$email)) {
                $this->message("邮件地址不合法!",'gift/default');
            }

            if(($this->user['email'] != $email) && $this->db->fetch_total('user'," email='$email' ")) {
                $this->message("此邮件地址已经注册!",'gift/default');
            }

            $gift = $_ENV['gift']->get($gid);
            if($this->user['credit2']<$gift['credit']) {
                $this->message("抱歉！您的财富值不足不能兑换该礼品!",'gift/default');
            }
           
            $_ENV['user']->update_gift($this->user['uid'],$realname,$email,$phone,$qq);
            $_ENV['gift']->addlog($this->user['uid'],$gid,$this->user['username'],$realname,$this->user['email'],$phone,$addr,$postcode,$gift['title'],$qq,$notes,$gift['credit']);
            $this->credit($this->user['uid'],0,-$gift['credit']);//扣除财富值
            $this->message("礼品兑换申请已经送出等待管理员审核！","gift/default");
        }
    }

}
?>