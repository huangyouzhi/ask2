<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_dbcontrol extends base {

    function admin_dbcontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('db');
    }

    /*数据库备份*/
    function onbackup() {
        set_time_limit(0);
        $filedir=ASK2_ROOT."/data/db_backup/";
        if(!isset($this->post['backupsubmit'])&&!isset($this->get[9])) {
            $sqlfilename=date("Ymd",$this->time)."_".random(8);
            $tables=$_ENV['db']->showtables();
            forcemkdir($filedir);
            $filename=$_ENV['db']->get_sqlfile_list($filedir);
            include template('dbbackup','admin');
        }else {
            $sqldump = '';
            $type=isset($this->post['type'])?$this->post['type']:$this->get[2];
            $sqlfilename=isset($this->post['sqlfilename'])?$this->post['sqlfilename']:rawurldecode($this->get[3]);
            $sizelimit=isset($this->post['sizelimit'])?$this->post['sizelimit']:intval($this->get[4]);
            $tableid = intval($this->get[5]);
            $startfrom = intval($this->get[6]);
            $volume = intval($this->get[7]) + 1;
            $compression=isset($this->post['compression'])?$this->post['compression']:intval($this->get[8]);
            $backupfilename=$filedir.$sqlfilename;
            $backupsubmit=1;
            $tables=array();
            if(substr(trim(ini_get('memory_limit')),0,-1)<32&&substr(trim(ini_get('memory_limit')),0,-1)>0) {
                @ini_set('memory_limit','32M');
            }
            if(!is_mem_available($sizelimit*1024*3)) {
                $this->message($sizelimit.'KB 大于PHP程序可用值,请设置较小分卷大小值','index.php?admin_db/backup');
            }
            switch($type) {
                case "full":
                    $tables=$_ENV['db']->showtables();
                    break;
                case "stand":
                    $tables=array(DB_TABLEPRE."category",DB_TABLEPRE."question",DB_TABLEPRE."answer",DB_TABLEPRE."user",DB_TABLEPRE."setting");
                    break;
                case "min":
                    $tables=array(DB_TABLEPRE."question",DB_TABLEPRE."answer");
                    break;
                case "custom":
                    if(!(bool)$this->post['tables']) {
                        $tables=$this->cache->read('backup_tables','0');
                    }else {
                        $tables=$this->post['tables'];
                        $this->cache->write('backup_tables', $tables);
                    }
                    break;
            }
            if($sizelimit<512) {
                $this->message('文件大小限制不要小于512K','BACK');
            }
            if(count($tables)==0) {
                $this->message('请先选择数据表!','BACK');
            }
            if(!file_exists($filedir)) {
                forcemkdir($filedir);
            }
            if(!iswriteable($filedir)) {
                $this->message('/data/db_backup 文件夹不可写!','index.php?admin_db-backup');
            }
            if(in_array(DB_TABLEPRE."usergroup",$tables)) {
                $num=array_search(DB_TABLEPRE."usergroup",$tables);
                $tables[$num]=$tables[0];
                $tables[0]=DB_TABLEPRE."usergroup";
            }
            if(in_array(DB_TABLEPRE."user",$tables)) {
                $num=array_search(DB_TABLEPRE."user",$tables);
                if($tables[0]==DB_TABLEPRE."usergroup") {
                    $tables[$num]=$tables[1];
                    $tables[1]=DB_TABLEPRE."user";
                }else {
                    $tables[$num]=$tables[0];
                    $tables[0]=DB_TABLEPRE."user";
                }
            }
            $complete = TRUE;
            for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++) {
                $result=$_ENV['db']->sqldumptable($tables[$tableid],$complete,$sizelimit, $startfrom, strlen($sqldump));
                $sqldump .= $result['tabledump'];
                $complete=$result['complete'];
                if($complete) {
                    $startfrom = 0;
                }else {
                    $startfrom = $result['startfrom'];
                }
            }
            $dumpfile = $backupfilename."_%s".'.sql';
            !$complete && $tableid--;
            if(trim($sqldump)) {
                $result=$_ENV['db']->write_to_sql($sqldump,$dumpfile,$volume);
                if(!$result) {
                    $this->message('无法写入sql文件,请返回','BACK');
                }else {
                    $url="index.php?admin_db/backup/$type/".rawurlencode($sqlfilename)."/$sizelimit/$tableid/$startfrom/$volume/$compression/$backupsubmit";
                    $this->message("<image src='css/default/loading.gif'><br />第 ".$volume.' 个文件已经完成!正在进入下一个备份!'."<script type=\"text/javascript\">setTimeout(\"window.location.replace('$url');\", 2000);</script>",'BACK');
                }
            }else {
                $volume--;
                if($compression && is_mem_available($sizelimit*1024*3*$volume)) {
                    $_ENV['db']->write_to_zip($backupfilename,$dumpfile,$volume);
                }
                $this->cache->remove('backup_tables');
                $this->message('数据备份成功！','admin_db/backup');
            }
        }
    }

    /*数据库导入*/
    function onimport() {
        set_time_limit(0);
        if(substr(trim(ini_get('memory_limit')),0,-1)<32&&substr(trim(ini_get('memory_limit')),0,-1)>0) {
            @ini_set('memory_limit','32M');
        }
        $filename=str_replace('*','.',$this->get[2]);
        $filenum=$this->get[3]?$this->get[3]:1;
        $filedir="./data/db_backup/";
        $filetype=$this->get[4]?$this->get[4]:substr($filename,-3);
        if($filetype!='zip'&&$filetype!='sql') {
            $this->message('文件格式不正确','BACK');
        }else {
            if($filenum==1) {
                if($filetype=='zip') {
                    require_once ASK2_ROOT.'/lib/zip.class.php';
                    $zip=new zip();
                    if(!$zip->chk_zip) {
                        $this->message('chkziperror','');
                    }
                    $zip->Extract($filedir.$filename,$filedir);
                    $filename=substr($filename,0,-4)."_1.sql";
                }else {
                    $num=strrpos($filename,"_");
                    $filename=substr($filename,0,$num)."_1.sql";
                }
            }
            if(file_exists($filedir.$filename)) {
                $sqldump=readfromfile($filedir.$filename);
                preg_match('/#\sVersion:\sask2\s([^\n]+)\n/i',$sqldump,$tversion);
                if($tversion[1]!=ASK2_VERSION) {
                    $this->message('导入的备份数据文件版本不正确','admin_db/backup');
                }
                $sqlquery = $_ENV['db']->splitsql($sqldump);
                unset($sqldump);
                foreach($sqlquery as $sql) {
                    $sql = $_ENV['db']->syntablestruct(trim($sql), $this->db->version() > '4.1', DB_CHARSET);
                    if($sql != '') {
                        $this->db->query($sql, 'SILENT');
                        if(($sqlerror = $this->db->error()) && $this->db->errno() != 1062) {
                            $this->db->halt('MySQL Query Error', $sql);
                        }
                    }
                }
                if($filetype=='zip') {
                    @unlink($filedir.$filename);
                }
                $filenum++;
                $num=strrpos($filename,"_");
                $filename=str_replace('.','*',substr($filename,0,$num)."_".$filenum.".sql");
                $this->message("<image src='css/default/loading.gif'><br />".'第 '.($filenum-1).' 个文件已经完成!正在进入下一个备份!', "admin_db/import/$filename/$filenum/$filetype");
            }else {
                $this->cache->remove('import_files');
                $this->message('导入数据成功!','admin_db/backup');
            }
        }
    }

    /*删除备份文件*/
    function onremove() {
        $filename=$this->get[2];
        $filename=str_replace('*','.',$filename);
        $filedir=ASK2_ROOT."/data/db_backup/".$filename;
        if(!iswriteable($filedir))$this->message('文件不可写!','admin_db/backup');
        if(file_exists($filedir)) {
            unlink($filedir);
            $this->message('删除文件成功!','admin_db/backup');
        }
    }

    /*表列表*/
    function ontablelist() {
        $dbversion=mysql_get_server_info();
        $ret = $list = array();
        $chip=0;
        $ret=$_ENV['db']->show_table_status();
        $count=count($ret);
        for ($i=0;$i<$count;$i++ ) {
            $res=$_ENV['db']->check_table($ret[$i]['Name']);
            $type = $dbversion>'4.1'?$ret[$i]['Engine']:$ret[$i]['Type'];
            $chartset = $dbversion>'4.1'?$ret[$i]['Collation']:'N/A';
            $tablelist[] = array('table'=>$ret[$i]['Name'],
                    'type'=>$type,
                    'rec_num'=>$ret[$i]['Rows'],
                    'rec_index' =>sprintf(" %.2f KB",$ret[$i]['Data_length']/1024),
                    'rec_chip'=>$ret[$i]['Data_free'] ,
                    'status'=>$res['Msg_text'],'chartset'=>$chartset);
            $chip+= $ret[$i]['Data_free'];
            if($tablelist[$i]['table']==DB_TABLEPRE."session") {
                $session_chip=$list[$i]['rec_chip'];
                $tablelist[$i]['rec_chip']="0";
                $tablelist[$i]['status']="OK";
            }
        }
        $number=$chip-$session_chip;
        include template('dboptimize','admin');
    }

    /*数据库优化*/
    function onoptimize() {
        $tables=$_ENV['db']->show_tables_like();
        $message='';
        foreach ($tables as $table) {
            $tag=$_ENV['db']->optimize_table($table);
            if ($tag==0) {
                $message .='表 '.$table.' 优化失败<br>';
            }else {
                $message .='表 '.$table.' 优化成功<br>';
            }
        }
        $this->message($message,'admin_db/tablelist');
    }

    /*数据库修复*/
    function onrepair() {
        $tables=$_ENV['db']->show_tables_like();
        $message='';
        foreach ($tables as $table) {
            $tag=$_ENV['db']->repair_table($table);
            if ($tag==0) {
                $message .='表 '.$table.' 修复失败<br>';
            }else {
                $message .='表 '.$table.' 修复成功<br>';
            }
        }
        $this->message($message,'admin_db/tablelist');
    }

    /*数据库SQL执行*/
    function onsqlwindow() {
        if(isset($this->post['sqlsubmit'])) {
            echo '<meta http-equiv="Content-Type" content="text/html;charset='.ASK2_CHARSET.'">';
            $sql = trim(stripslashes($this->post['sqlwindow']));
            $sqltype=$this->post['sqltype'];
            if(''==$sql) {
                echo 'SQL语句为空！';
            }elseif(eregi("drop(.*)table",$sql) || eregi("drop(.*)database",$sql)) {
                echo 'drop语句不允许在这里执行。';
            }elseif(eregi("^select ",$sql)) {
                $query=$this->db->query($sql);
                if(!$query) {
                    echo 'SQL语句执行失败！';
                }else {
                    $num=$this->db->num_rows($query);
                    if($num<=0) {
                        echo '运行SQL：'.$sql.'，无返回记录！';
                    }else {
                        echo '运行SQL：'.$sql.'，共有'.$num.'条记录，最大返回50条！';
                        $j = 0;
                        while(($row=$this->db->fetch_array($query)) && $j<50) {
                            $j++;
                            echo '<p style=" background-color:#d3e8fd;">'.'记录：'.$j.'</p>';
                            foreach($row as $k=>$v) {
                                echo "<font color='red'>{$k} : </font>{$v}<br/>\r\n";
                            }
                        }
                    }
                }
            }else {
                if(1==$sqltype) {
                    $sql = str_replace("\r","",$sql);
                    $sqls = split(";[ \t]{0,}\n",$sql);
                    $i=0;
                    foreach($sqls as $q) {
                        $q = trim($q);
                        if($q=="") {
                            continue;
                        }
                        if((bool)$this->db->query($q)) {
                            $i++;
                            echo $q.'<br/>';
                            echo 'SQL语句执行成功！';
                        }else {
                            echo '执行： <font color="blue">'.$q.'</font> 出错';
                        }
                    }
                }else {
                    if($query=$this->db->query($sql)) {
                        echo 'SQL语句执行成功！';
                    }else {
                        echo 'SQL语句执行失败！';
                    }
                }
            }
            exit;
        }
        include template('sqlwindow','admin');
    }


    /*下载备份*/
    function ondownloadfile() {
        $filename=str_replace('*','.',$this->get[2]);
        header('content-disposition: attachment; filename='.$filename);
        echo readfromfile('data/db_backup/'.$filename);
    }





}
?>