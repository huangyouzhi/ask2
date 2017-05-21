<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_cmscontrol extends base {

    function admin_cmscontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
    }

    function onsetting() {
        if (isset($this->post['submit'])) {
            $this->setting['cms_open'] = intval($this->post['cms_open']);
            $config = "<?php \r\n";
            if($this->post['cms_db_config']){
                $config .= trim($this->post['cms_db_config'])."\r\n";
            }
            if($this->post['cms_db_article']){
                $config .= trim($this->post['cms_db_article']);
            }
            writetofile(ASK2_ROOT . '/data/cms.config.inc',  tstripslashes($config));
            $message = 'cms参数配置完成！';
            $_ENV['setting']->update($this->setting);
        }
        include template("cms_setting", "admin");
    }

}
