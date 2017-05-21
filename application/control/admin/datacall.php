<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_datacallcontrol extends base {

    function admin_datacallcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('datacall');
        $this->load('question');
        $this->load('category');
    }

    function ondefault() {
        $datacalllist = $_ENV['datacall']->get_list();
        include template('datacalllist','admin');
    }



    function onadd() {
        if(isset($this->post['submit'])) {
            $expressionarr = array();
            $expressionarr['tpl'] = base64_encode($this->post['tpl']);
            $expressionarr['status'] = $this->post['status'];
            $categorystr='';
            if(isset($this->post['category1'])) {
                $categorystr .= intval($this->post['category1']).':';
            }
            if(isset($this->post['category2'])) {
                $categorystr .= intval($this->post['category2']).':';
            }
            if(isset($this->post['category2'])) {
                $categorystr .= intval($this->post['category2']).':';
            }
            $expressionarr['category']=$categorystr;
            $expressionarr['cachelife']=intval($this->post['cachelife']);
            $expressionarr['maxbyte']=intval($this->post['maxbyte']);
            $expressionarr['start']=$this->post['start'];
            $expressionarr['limit']=$this->post['limit'];
            $expression = serialize($expressionarr);
            $_ENV['datacall']->add(trim($this->post['title']),$expression);
            $this->ondefault();
        }else {
            $categoryjs=$_ENV['category']->get_js();
            $status_list = array(array('all','全部问题'),array(1,'待解决'),array(2,'已解决'),array(4,'悬赏'));
            include template('adddatacall','admin');
        }

    }

    function onremove() {
        if(isset($this->post['delete'])) {
            $ids = implode($this->post['delete'],",");
            $_ENV['datacall']->remove_by_id($ids);
            $this->ondefault();
        }
    }

    function onedit() {
        $id = isset($this->get[2])?intval($this->get[2]):intval($this->post['id']);
        if(isset($this->post['submit'])) {
            $expressionarr = array();
            $expressionarr['tpl'] = trim(base64_encode($this->post['tpl']));
            $expressionarr['status'] = $this->post['status'];
            $categorystr='';
            if(isset($this->post['category1'])) {
                $categorystr .= intval($this->post['category1']).':';
            }
            if(isset($this->post['category2'])) {
                $categorystr .= intval($this->post['category2']).':';
            }
            if(isset($this->post['category2'])) {
                $categorystr .= intval($this->post['category2']).':';
            }
            $expressionarr['category']=$categorystr;
            $expressionarr['cachelife']=intval($this->post['cachelife']);
            $expressionarr['maxbyte']=intval($this->post['maxbyte']);
            $expressionarr['start']=$this->post['start'];
            $expressionarr['limit']=$this->post['limit'];
            $expression = serialize($expressionarr);
            $_ENV['datacall']->update($id,trim($this->post['title']),$expression);
            $message = '设置编辑成功!';
        }
        $datacall = $_ENV['datacall']->get($id);
        if($datacall) {
            $expressionarr = unserialize($datacall['expression']);
            $tpl = stripslashes(base64_decode($expressionarr['tpl']));
            $cid1=0;
            $cid2=0;
            $cid3=0;
            if('' != $expressionarr['category']) {
                $category =explode(":",substr($expressionarr['category'],0,-1));
                isset($category[0]) && $cid1=$category[0];
                isset($category[1]) && $cid2=$category[1];
                isset($category[2]) && $cid3=$category[2];
            }
            $categoryjs=$_ENV['category']->get_js();
            $status_list = array(array('all','全部问题'),array(1,'待解决'),array(2,'已解决'),array(4,'悬赏'));
            include template('editdatacall','admin');
        }
    }

    //生成js代码
    //<script type="text/javascript" src="http://www.ask.com/index.php?js/view/1"></script>
    //此函数不存在，需要在后台用弹出div展示即可

}
?>