<?php

/* 系统定时任务处理 */
!defined('IN_ASK2') && exit('Access Denied');

class crontabmodel {

    var $db;
    var $base;

    function crontabmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 统计所有分类下的问题数目 */

    function sum_category_question($crontab, $force=0) {
        $curtime = $this->base->time;
        if (($crontab['lastrun'] + $crontab['minute'] * 60) < $curtime || $force) {
            /* 第一步：统计每个分类下的问题数目 */
            $query = $this->db->query("SELECT c.id,c.pid,count(*) as num FROM " . DB_TABLEPRE . "question as q," . DB_TABLEPRE . "category as c WHERE c.id=q.cid AND q.status !=0 GROUP BY c.id");
            //第二步:依次更新所有分类的问题数目
            while ($category = $this->db->fetch_array($query)) {
                $this->db->query("UPDATE " . DB_TABLEPRE . "category SET questions=" . $category['num'] . " WHERE `id`=" . $category['id']);
            }
            if ($crontab) {
                $nextrun = $curtime + $crontab['minute'] * 60;
                $this->db->query("UPDATE " . DB_TABLEPRE . "crontab SET lastrun=$curtime,nextrun=$nextrun WHERE id=" . $crontab['id']);
            }
            //第三步:更新缓存文件
            @unlink(ASK2_ROOT . "/data/cache/categorylist.php");
            @unlink(ASK2_ROOT . "/data/cache/category.php");
            @unlink(ASK2_ROOT . "/data/cache/crontab.php");
        }
    }

}

?>
