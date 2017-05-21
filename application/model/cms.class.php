<?php
!defined('IN_ASK2') && exit('Access Denied');
require_once ASK2_ROOT.'/data/cms.config.inc';
class cmsmodel {
    var $dbcms;
    var $db;
    var $base;

    function cmsmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->dbcms = new db(CMS_DB_SERVER,CMS_DB_USER,CMS_DB_PASSWORD,CMS_DB_NAME, DB_CHARSET, DB_CONNECT);
    }
    
    function get_list() {
        $articlelist = array();
        $query = $this->dbcms->query("SELECT * FROM " .CMS_DB_ARTICLE_TBNAME . " WHERE 1=1 " .CMS_DB_ARTICLE_SORT);
        while ($article = $this->dbcms->fetch_array($query)) {
            $article['title'] = $article[CMS_DB_ARTICLE_FIELD];
            $article['href'] = str_replace("[articleid]", $article[CMS_DB_ARTICLE_PRIMARY],CMS_ARTICLE_URL);
            $articlelist[] = $article;
        }
        return $articlelist;
    }

}
