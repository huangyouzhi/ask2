<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_giftcontrol extends base {

    function admin_giftcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('gift');
        $this->load('setting');
    }

    function ondefault($msg = '') {
        $msg && $message = $msg;
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $giftlist = $_ENV['gift']->get_list($startindex, $pagesize);
        $giftnum = $this->db->fetch_total('gift');
        $departstr = page($giftnum, $pagesize, $page, "admin_gift/default");
        $gift_range = unserialize($this->setting['gift_range']);
        include template('giftlist', 'admin');
    }

    function onadd() {
        if (isset($this->post['submit'])) {
            $title =strip_tags( $this->post['giftname']);
            $desrc = strip_tags($this->post['giftdesrc']);
            $credit = intval($this->post['giftprice']);
            $imgname =strip_tags( strtolower($_FILES['imgurl']['name']));
            if ('' == $title || !$credit) {
                $this->ondefault('请正确填写礼品相关信息！');
                exit;
            }
            $type = substr(strrchr($imgname, '.'), 1);
            if (!isimage($type)) {
                $this->ondefault('图片格式不支持，目前仅支持jpg、gif、png格式！');
                exit;
            }
            $filepath = '/data/attach/giftimg/gift' . random(6, 0) . '.' . $type;
            forcemkdir(ASK2_ROOT . '/data/attach/giftimg');
            if (move_uploaded_file($_FILES['imgurl']['tmp_name'], ASK2_ROOT . $filepath)) {
                $_ENV['gift']->add($title, $desrc, $filepath, $credit);
                $this->ondefault('添加成功！');
            } else {
                $this->ondefault('服务器忙，请稍后再试！');
            }
        } else {
            include template('addgift', 'admin');
        }
    }

    function onedit() {
        $gid = intval($this->get[2]) ? $this->get[2] : $this->post['id'];
        if (isset($this->post['submit'])) {
            $title = $this->post['giftname'];
            $desrc = $this->post['giftdesrc'];
            $credit = intval($this->post['giftprice']);
            $imgname = strtolower($_FILES['imgurl']['name']);
            if ('' == $title || !$credit) {
                $message = '请正确填写礼品相关信息';
                $type = 'errormsg';
                include template('addgift', 'admin');
                exit;
            }

            $type = substr(strrchr($imgname, '.'), 1);
            if (!empty($_FILES['imgurl']['tmp_name']) && (!isimage($type))) {
                $message = '图片格式不支持，目前仅支持jpg、gif、png格式！';
                $type = 'errormsg';
                include template('addgift', 'admin');
                exit;
            }


            $filepath = '/data/attach/giftimg/gift' . random(6, 0) . '.' . $type;
            forcemkdir(ASK2_ROOT . '/data/attach/giftimg');
            if (!empty($_FILES['imgurl']['tmp_name']) && (!move_uploaded_file($_FILES['imgurl']['tmp_name'], ASK2_ROOT . $filepath))) {
                $message = '服务器忙，请稍后再试！';
                $type = 'errormsg';
                include template('addgift', 'admin');
                exit;
            }
            empty($_FILES['imgurl']['tmp_name']) && $filepath = $this->post['imgpath'];


            $_ENV['gift']->update($title, $desrc, $filepath, $credit, $gid);
            $message = "修改成功!";
        }

        $gift = $_ENV['gift']->get($gid);
        include template('addgift', 'admin');
    }

    function onaddrange() {
        $rangelist = unserialize($this->setting['gift_range']);
        if (isset($this->post['submit'])) {
            $ranges = $this->post['gift_range'];
            $rangesize = count($ranges);
            $giftrange = array();
            for ($i = 0; $i < $rangesize; $i++) {
                if ($i % 2 == 0 && ($ranges[$i] != NULL || $ranges[$i + 1] != NULL))
                    $giftrange[$ranges[$i]] = $ranges[$i + 1];
            }

            $rangelist = $giftrange;
            $this->setting['gift_range'] = serialize($giftrange);
            $_ENV['setting']->update($this->setting);
            $message = '设置成功！';
        }
        include template('giftrange', 'admin');
    }

    function onnote() {
        if (isset($this->post['submit'])) {
            $this->setting['gift_note'] = $this->post['note'];
            $_ENV['setting']->update($this->setting);
            $message = '设置公告成功！';
        }
        include template('giftnote', 'admin');
    }

    function onremove() {
        $message = '没有选择礼品！';
        if (isset($this->post['gid'])) {
            $gids = implode(",", $this->post['gid']);
            $_ENV['gift']->remove_by_id($gids);
            $message = '礼品删除成功！';
            unset($this->get);
        }
        $this->ondefault($message);
    }

    function onavailable() {
        if (isset($this->post['gid'])) {
            $gids = implode(",", $this->post['gid']);
            $_ENV['gift']->update_available($gids, $this->get[2]);
            $message = $this->get[2] ? '礼品设为可用成功!' : '礼品设置过期成功!';
            unset($this->get);
            $this->ondefault($message);
        }
    }

    function onlog($msg = '') {
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $loglist = $_ENV['gift']->getloglist($startindex, $pagesize);

        $giftlognum = $this->db->fetch_total('giftlog');
        $departstr = page($giftnum, $pagesize, $page, "admin_gift/log");
        $msg && $message = $msg;
        $gift_range = unserialize($this->setting['gift_range']);
        include template("giftloglist", 'admin');
    }

    function onsend() {
        if (isset($this->post['id'])) {
            $this->load("message");
            $ids = implode(",", $this->post['id']);
            $_ENV['gift']->update_gift_status($ids, $this->get[2]);
            $message = '礼品成功设置为已送出！';
            $msgfrom = $this->setting['site_name'] . '管理员';
            foreach ($this->post['id'] as $logid) {
                $giftlog = $_ENV['gift']->getlog($logid);
                $_ENV['message']->add($msgfrom, 0, $giftlog['uid'],'您在礼品商店兑换的商品"'.$giftlog['giftname'].'"已经发货了，请注意查收!','您在礼品商店兑换的商品"'.$giftlog['giftname'].'已经发货了，请注意查收!<br />如长时间未收到兑换商品请与管理员联系!"');
            }
            unset($this->get);
            $this->onlog($message);
        }
    }

    function onsearch() {

        @$page = max(1, intval($this->get[4]));
        $range = isset($this->post['pricerange']) ? $this->post['pricerange'] : $this->get[2];
        $giftname = isset($this->post['giftname']) ? $this->post['giftname'] : $this->get[3];
        $pagesize = 1;
        $startindex = ($page - 1) * $pagesize;
        $rangesql = '';
        $ranges = explode("-", $range);
        $giftlist = $_ENV['gift']->get_by_range_name($ranges, $giftname, $startindex, $pagesize);
        (count($ranges) > 1) && $rangesql = "AND `credit`>=$ranges[0] AND `credit`<=$ranges[1]";
        $rownum = $this->db->fetch_total('gift', " `title` LIKE '$giftname%' $rangesql");
        $departstr = page($rownum, $pagesize, $page, "admin_gift/search/$range/$giftname");
        $gift_range = unserialize($this->setting['gift_range']);
        include template('giftlist', 'admin');
    }

    function onlogsearch() {
        $pricerange = isset($this->get[2]) ? $this->get[2] : $this->post['pricerange'];
        $giftname = isset($this->get[3]) ? $this->get[3] : $this->post['giftname'];
        $username = isset($this->get[4]) ? $this->get[4] : $this->post['username'];
        $datestart = isset($this->get[5]) ? $this->get[5] : $this->post['srchregdatestart'];
        $dateend = isset($this->get[6]) ? $this->get[6] : $this->post['srchregdateend'];
        @$page = max(1, intval($this->get[7]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $loglist = $_ENV['gift']->list_by_searchlog($pricerange, $giftname, $username, $datestart, $dateend, $startindex, $pagesize);
        $giftlognum = $_ENV['gift']->rownum_by_searchlog($pricerange, $giftname, $username, $datestart, $dateend);
        $departstr = page($giftlognum, $pagesize, $page, "admin_gift/logsearch/$pricerange/$giftname/$username/$datestart/$dateend");
        $gift_range = unserialize($this->setting['gift_range']);
        include template("giftloglist", 'admin');
    }

}

?>