<?php

!defined('IN_ASK2') && exit('Access Denied');

class attachmodel {

    var $db;
    var $base;

    function attachmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function movetmpfile($attach,$targetfile) {
        forcemkdir(dirname($targetfile));
        if(copy($attach['tmp_name'],$targetfile) || move_uploaded_file($attach['tmp_name'],$targetfile)) {
            return 1;
        }
        if( is_readable($attach['tmp_name'])) {
            $fp = fopen($attach['tmp_name'], 'rb');
            flock($fp, 2);
            $attachedfile = fread($fp, $attach['size']);
            fclose($fp);
            $fp = fopen($targetfile, 'wb');
            flock($fp,2);
            if(fwrite($fp, $attachedfile)) {
                unlink($attach['tmp_name']);
            }
            fclose($fp);
            return 1;
        }
        return 0;
    }


    function add($filename,$ftype,$fsize,$location,$isimage=1) {
        $uid=$this->base->user['uid'];
        $this->db->query("INSERT INTO ".DB_TABLEPRE."attach(time,filename,filetype,filesize,location,isimage,uid)  VALUES ({$this->base->time},'$filename','$ftype','$fsize','$location',$isimage,$uid)");
        return $this->db->insert_id();
    }



}
?>