<?php

!defined('IN_ASK2') && exit('Access Denied');

class api_articlecontrol extends base {

	var $apikey='';
		var $whitelist;
		var $domain;
	//构造函数
    function api_articlecontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load('topic');
     $this->whitelist="list,newqlist,hotqlist,hotalist";
     $this->domain=isset($this->setting['wap_domain'])&&trim($this->setting['wap_domain'])!='' ? "http://".$this->setting['wap_domain']."/":SITE_URL;
    }
    function onlist(){
    	
          $content = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic order by id desc LIMIT 0,5");
        while ($topic = $this->db->fetch_array($query)) {
           
  //$topic['viewtime'] = tdate($topic['viewtime']);
  $description=cutstr(strip_tags($topic['describtion']),120,'');

  if(strstr($topic['image'],'http')){
  	 $content[] = array("Title"=>$topic['title'], "Description"=>$description, "PicUrl"=> $topic['image'], "Url" =>$this->domain.'article-'.$topic['id']);
  }else{
  	 $content[] = array("Title"=>$topic['title'], "Description"=>$description, "PicUrl"=> $this->domain.$topic['image'], "Url" =>$this->domain.'?article-'.$topic['id']);
  }
            
        }
        echo json_encode($content);
    }
    function onnewqlist(){
    	
          $questionlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "question where status=1 order by id desc LIMIT 0,5");

           
  
  $description=cutstr(strip_tags($question['describtion']),120,'');
          while ($question = $this->db->fetch_array($query)) {
         
           $question['describtion']=cutstr(strip_tags($question['describtion']),120,'');
 $question['avatar']=get_avatar_dir($question['authorid']);
            $question['url'] =$this->domain.'?q-'.$question['id'].'.html';
            $questionlist[] = $question;
        }
//    
       echo json_encode($questionlist);

    	
//  $nosolvelist=$this->fromcache('nosolvelist');
//    
//   $i=0;
//          foreach ($nosolvelist as $key=>$val){
//          	if($i>=4){
//          		break;
//          	}
//          	$nosolvelist[$key]['url']=$this->domain.'?q-'.$nosolvelist[$key]['id'].'.html';
//          	 $nosolvelist[$key]['avatar']=get_avatar_dir($nosolvelist[$key]['authorid']);
//          	 ++$i;   
//          }
//         
//        echo json_encode($nosolvelist);
    }
   function onhotqlist(){
    	
          $attentionlist=$this->fromcache('attentionlist');
      $i=0;
          foreach ($attentionlist as $key=>$val){
          if($i>=4){
          		break;
          	}
          	$attentionlist[$key]['url']=$this->domain.'?q-'.$attentionlist[$key]['id'].'.html';
          	 $attentionlist[$key]['avatar']=get_avatar_dir($attentionlist[$key]['authorid']);
          	  ++$i;   
          }
         
        echo json_encode($attentionlist);
    }
   function onhotalist(){
    	$content = array();
          $modellist=$this->fromcache('hottopiclist');
    
        
          $i=1;
          foreach ($modellist as $key=>$val){
          	if($i>=6)break;
          //$topic['viewtime'] = tdate($topic['viewtime']);
  $description=cutstr(strip_tags($modellist[$key]['describtion']),120,'');

  if(strstr($modellist[$key]['image'],'http')){
  	 $content[] = array("Title"=>$modellist[$key]['title'], "Description"=>$description, "PicUrl"=> $modellist[$key]['image'], "Url" =>$this->domain.'article-'.$modellist[$key]['id']);
  }else{
  	 $content[] = array("Title"=>$modellist[$key]['title'], "Description"=>$description, "PicUrl"=> 'http://' . $_SERVER['HTTP_HOST'].$modellist[$key]['image'], "Url" =>$this->domain.'?article-'.$modellist[$key]['id']);
  }
  $i++;
          }
         
        echo json_encode($content);
    }
}

?>