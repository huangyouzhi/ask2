<?php

!defined('IN_ASK2') && exit('Access Denied');

class keywordsmodel {

    var $db;
    var $base;

    function keywordsmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($start=0,$limit=20){
        $wordlist = array();
        $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."keywords  ORDER BY `id` DESC LIMIT $start,$limit");
        while($word = $this->db->fetch_array($query)){
       
        	$word['num']=0;
            $wordlist[] = $word;
        }
        return $wordlist;
    }
    function add($wids,$finds,$replacements,$admin){
        $wsize = count($wids);
        for($i=0;$i<$wsize;$i++){
            if($wids[$i]){
                $this->db->query("UPDATE ".DB_TABLEPRE."keywords SET `find`='$finds[$i]',`replacement`='$replacements[$i]' WHERE `id`=$wids[$i]");
            }else{
                $finds[$i] && $this->db->query("INSERT INTO `".DB_TABLEPRE."keywords` SET `admin`='$admin',`find`='$finds[$i]',`replacement`='$replacements[$i]'");
            }
        }   
    }

    function multiadd($lines,$admin){
        $sql = "INSERT INTO `".DB_TABLEPRE."keywords`(`admin` ,`find` , `replacement`) VALUES ";
        foreach ($lines as $line){
            $line=str_replace(array("\r\n", "\n", "\r"), '', $line);
            if(empty($line))continue;
            @list($find,$replacement)=explode('=' , $line);
            $sql .= "('$admin','$find', '$replacement'),";
        }
        $sql=substr($sql,0,-1);
        $this->db->query($sql);
    }

    function remove_by_id($ids){
        $this->db->query("DELETE FROM ".DB_TABLEPRE."keywords WHERE `id` IN ($ids)");
    }

}
?>