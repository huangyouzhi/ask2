<?php

!defined('IN_ASK2') && exit('Access Denied');

class informmodel {

    var $db;
    var $base;
    var $reasons= array(
            '含有反动的内容',
            '含有人身攻击的内容',
            '含有广告性质的内容',
            '涉及违法犯罪的内容',
            '含有违背伦理道德的内容',
            '含色情、暴力、恐怖的内容',
            '含有恶意无聊灌水的内容'
    );

    function informmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function get($qid) {
        return $this->db->fetch_first("SELECT * FROM ".DB_TABLEPRE."inform WHERE qid=$qid");
    }

    function add($qid,$qtitle,$uid,$username,$aid,$title,$content,$keywords) {
        $time = $this->base->time;
        //echo "INSERT INTO ".DB_TABLEPRE."inform SET uid=$uid,aid=$aid,qid=$qid,title='$title',qtitle='$qtitle',username='$username',content='$content',keywords='$keywords',`time`=$time";
        //exit();
        $this->db->query("INSERT INTO ".DB_TABLEPRE."inform SET uid=$uid,aid=$aid,qid=$qid,title='$title',qtitle='$qtitle',username='$username',content='$content',keywords='$keywords',`time`=$time");
    }

    function update($title,$content,$keywords,$qid) {
        $this->db->query("UPDATE ".DB_TABLEPRE."inform SET title='$title',content='$content',keywords='$keywords',counts=counts+1 WHERE qid=$qid");
    }

    function get_list($start=0,$limit=10) {
        $informlist=array();
        $query=$this->db->query("SELECT * FROM ".DB_TABLEPRE."inform ORDER BY time DESC LIMIT $start,$limit");
        while($inform=$this->db->fetch_array($query)) {
            $inform['time']=tdate($inform['time'],3,0);
          //  $inform['content']=implode(';',unserialize($inform['content']));
            //$inform['reasons']=$this->get_reasons(unserialize($inform['keywords']));
            switch ($inform['keywords']){
            	case '1':
            		$inform['keywords']="检举内容";
            		break;
            			case '2':
            				$inform['keywords']="检举用户";
            		break;
            		default:
            			$inform['keywords']="检举内容";
            			break;
            }
               switch ($inform['title']){
            	case '4':
            		$inform['title']="广告推广";
            		break;
            			case '5':
            				$inform['title']="恶意灌水";
            		break;
            		case '6':
            		$inform['title']="回答内容与提问无关";
            		break;
            			case '7':
            				$inform['title']="抄袭答案";
            		break;
            			case '8':
            				$inform['title']="其它";
            		break;
            		default:
            			$inform['title']="恶意灌水";
            			break;
            }
            $informlist[]=$inform;
        }
        return $informlist;
    }
    function get_reasons($keys){
        $strreason = '';
        foreach ($keys as $key){
            $strreason .=','. $this->reasons[$key];
        }
        return substr($strreason,1);
    }

    function remove_by_id($qids){
        $this->db->query("DELETE FROM ".DB_TABLEPRE."inform WHERE `qid` IN ('$qids')");
    }

}
?>
