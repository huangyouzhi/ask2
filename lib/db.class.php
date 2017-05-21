<?php

class db {

    var $mlink;

    function db($dbhost, $dbuser, $dbpw, $dbname = '',$dbcharset='utf8', $pconnect=0) {
        if($pconnect) {
            if(!$this->mlink = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
                $this->halt('Can not connect to MySQL');
            }
        } else {
            if(!$this->mlink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
                $this->halt('Can not connect to MySQL');
            }
        }
        if($this->version()>'4.1') {
            if('utf-8'==strtolower($dbcharset)) {
                $dbcharset='utf8';
            }
            if($dbcharset) {
                mysql_query("SET character_set_connection=$dbcharset, character_set_results=$dbcharset, character_set_client=binary", $this->mlink);
            }
            if($this->version() > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->mlink);
            }
        }
        if($dbname) {
            mysql_select_db($dbname, $this->mlink);
        }
    }

    function select_db($dbname) {
        return mysql_select_db($dbname, $this->mlink);
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return (is_resource($query))? mysql_fetch_array($query, $result_type) :false;
    }

    function result_first($sql) {
        $query = $this->query($sql);
        return $this->result($query, 0);
    }

    function fetch_first($sql) {
        $query = $this->query($sql);
        return $this->fetch_array($query);
    }

    function update_field($table,$field,$value,$where) {
        return $this->query("UPDATE ".DB_TABLEPRE."$table SET $field='$value' WHERE $where");
    }

    function fetch_total($table,$where='1') {
        return $this->result_first("SELECT COUNT(*) num FROM ".DB_TABLEPRE."$table WHERE $where");
    }

    function query($sql, $type = '') {
        global $debug ,$querynum;
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
        if(!($query = $func($sql, $this->mlink)) && $type != 'SILENT') {
            $this->halt(mysql_error(),$debug);
        }
        $querynum++;
        return $query;
    }

    function affected_rows() {
        return mysql_affected_rows($this->mlink);
    }

    function error() {
        return (($this->mlink) ? mysql_error($this->mlink) : mysql_error());
    }

    function errno() {
        return intval(($this->mlink) ? mysql_errno($this->mlink) : mysql_errno());
    }

    function result($query, $row) {
        $query = @mysql_result($query, $row);
        return $query;
    }

    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insert_id() {
        return ($id = mysql_insert_id($this->mlink)) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
    }

    function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    function fetch_fields($query) {
        return mysql_fetch_field($query);
    }

    function fetch_all($sql, $id = '') {
        $arr = array();
        $query = $this->query($sql);
        while($data = $this->fetch_array($query)) {
            $id ? $arr[$data[$id]] = $data : $arr[] = $data;
        }
        return $arr;
    }

    function version() {
        return mysql_get_server_info($this->mlink);
    }

    function close() {
        return mysql_close($this->mlink);
    }

    function halt($msg, $debug=true) {
        if($debug) {
            echo "<html>\n";
            echo "<head>\n";
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
            echo "<title>$msg</title>\n";
            echo "<br><font size=\"6\" color=\"red\"><b>$msg</b></font>";
            exit();
        }
    }
}

?>