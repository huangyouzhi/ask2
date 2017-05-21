<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_totalsetcontrol extends base {

    function admin_totalsetcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
    }

    function onindex() {
        if (isset($this->post['submit'])) {
        	  $this->setting['mobile_shang'] =isset($this->post['mobile_shang']) ? doubleval($this->post['mobile_shang']):0.1;
            $this->setting['list_topdatanum'] = intval($this->post['list_topdatanum']);
              $this->setting['admin_list_default'] = intval($this->post['admin_list_default']);
           $this->setting['list_answernum'] = intval($this->post['list_answernum']);
           $this->setting['cancopy'] = intval($this->post['cancopy']);
            $this->setting['jingyan'] = intval($this->post['jingyan']);
            $_ENV['setting']->update($this->setting);
                	  cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
        }
        include template("setting_set", "admin");
    }

}
