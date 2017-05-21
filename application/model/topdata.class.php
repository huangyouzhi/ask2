<?php

!defined('IN_ASK2') && exit('Access Denied');

class topdatamodel {

    var $db;
    var $base;

    function topdatamodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function get_list($start = 0, $limit = 10) {
        $mdlist = array();
        $query = $this->db->query("select * from " . DB_TABLEPRE . "topdata order by  time desc limit $start,$limit");
        while ($md = $this->db->fetch_array($query)) {
            switch ($md['type']){
            	 	case 'note':
            		 $note=$this->getnote($md['typeid']);
            		  $md['model']=$note;
            		 $md['title']=$note['title'];
            		  $md['views']=$note['views'];
            		   $md['answers']=$note['comments'];
            		   $md['attentions']=0;
            		 	$md['image']=getfirstimg($note['content']);
            		$md['description']=cutstr( checkwordsglobal(strip_tags( $note['content'])), 240,'...');
            			$md['url']= urlmap('note/view/' . $note['id'], 2);
            		break;
            	case 'qid':
            		 $question=$this->getquestionbyqid($md['typeid']);
            		  $md['model']=$question;
            		 $md['title']=$question['title'];
            		  $md['views']=$question['views'];
            		   $md['answers']=$question['answers'];
            		   $md['attentions']=$question['attentions'];
            		 	$md['image']=getfirstimg($question['description']);
            		$md['description']=cutstr( checkwordsglobal(strip_tags( $question['description'])), 240,'...');
            			$md['url']= urlmap('question/view/' . $question['id'], 2);
            		break;
            		case 'aid':
            			 $answer=$this->getanswer($md['typeid']);
            				  $md['model']=$answer;
            		 $md['title']=$answer['title'];
            		$md['description']=cutstr( checkwordsglobal(strip_tags( $answer['content'])), 240,'...');
            			$md['url']= urlmap('question/view/' . $question['id'], 2);
            				$md['image']=getfirstimg($answer['content']);
            		break;
            		case 'topic':
            			 $topic=$this->gettopic($md['typeid']);
            			 $md['model']=$topic;
            		 $md['title']=$topic['title'];
            		   $md['views']=$topic['views'];
            		   $md['answers']=$topic['articles'];
            		    $md['attentions']=$topic['likes'];
            		$md['description']=cutstr( checkwordsglobal(strip_tags( $topic['describtion'])), 240,'...');
            			$md['url']= urlmap('topic/getone/' . $topic['id'], 2);
            				$md['image']=$topic['images'];
            		break;
            		
            		
            }
              $md['url'] = url($md['url']);
              $md['format_time'] = tdate($md['time']);
            $mdlist[] = $md;
        }
        return $mdlist;
    }
    function getnote($id) {
        $note = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "note WHERE id='$id'");
        $note['format_time'] = tdate($note['time'], 3, 0);
           $note['title'] = checkwordsglobal($note['title']);
        $note['content'] = checkwordsglobal($note['content']);
          $note['artlen'] = strlen(strip_tags($note['content']));
          $note['avatar']=get_avatar_dir($note['authorid']);
        return $note;
    }
    
    /* 根据aid获取一个答案的内容，暂时无用 */

    function getanswer($id) {
        $answer= $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "answer WHERE id='$id'");
          $answer['avatar'] = get_avatar_dir($answer['authorid']);
      
        return $answer;
    }
    function getquestionbyqid($id) {
        $question = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "question WHERE id='$id'");
       $question['avatar'] = get_avatar_dir($question['authorid']);
        return $question;
    }
    function gettopic($id) {
         $topic =  $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE id='$id'");
         $topic['avatar'] = get_avatar_dir($topic['authorid']);
    
        return $topic;
    }
    function add($typeid, $type,$order=1) {
        $this->remove($typeid, $type);
        $time=time();
      //  runlog('addtopdata', "INSERT INTO ".DB_TABLEPRE."topdata SET typeid=$typeid,type='$type',order=$order,`time`=$time");
       // $this->db->query('INSERT INTO ' . DB_TABLEPRE . "topdata(typeid,type,order,time) values ('$typeid','$type','$order','{$this->base->time}')");

        $this->db->query("INSERT INTO ".DB_TABLEPRE."topdata SET typeid=$typeid,type='$type',`time`=$time");
    
        return $this->db->insert_id();
    }

    function update($typeid,$type,$orderid) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "topdata SET order=$orderid WHERE `typeid`='$typeid' and type='$type' ");
    }

  
    function remove_by_id($ids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "topdata` WHERE `id` IN ($ids)");
    }
  function remove($typeid,$type) {
  	//runlog('detop', "DELETE FROM `" . DB_TABLEPRE . "topdata` WHERE `typeid`=$typeid and type='$type' ");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "topdata` WHERE `typeid`=$typeid and type='$type' ");
    }

}

?>
