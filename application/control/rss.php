<?php

!defined('IN_ASK2') && exit('Access Denied');

class rsscontrol extends base {

	var $whitelist;
    function rsscontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('category');
        $this->load('question');
        $this->load('answer');
        $this->load("tag");
         $this->load("topic");
         $this->whitelist="articlelist,tag,userspace";
    }
    
    /*
	分类下的RSS
	rss/category/1/1
    */
    function oncategory() {
    	
        $cid=$this->get[2];
        $status=isset($this->get[3])?$this->get[3]:'all';
        $category=$_ENV['category']->get($cid); //得到分类信息
        $cfield='cid'.$category['grade'];	//查询条件
        $questionlist=$_ENV['question']->list_by_cfield_cvalue_status($cfield,$cid,$status,0,20);//问题列表数据
        $this->writerss($questionlist,'分类'.$category['name'].$this->statusarray[$status].'问题');
    }
    /*
	列表下的RSS
	rss/list/1
    */
    function onlist() {
        $status=isset($this->get[2])?$this->get[2]:'all';
        $questionlist=$_ENV['question']->list_by_cfield_cvalue_status('',0,$status,0,200);//问题列表数据
        $this->writerss2($questionlist,$this->statusarray[$status].'问题');
    }
       /*
	列表下的RSS
	rss/articlelist/1
    */
    function onarticlelist() {
        
        
         $topiclist = $_ENV['topic']->get_list(2, 0, 200);//文章列表数据
          $this->writerssarticle($topiclist,'最新文章资讯');
         
    }
    //tag标签
    function ontag(){
    	  
        $taglist = $_ENV['tag']->get_list(0, 600);
        $this->writetag($taglist,'站内标签');
    }
        //用户
    function onuserspace(){
    	  
       $userlist = $_ENV['user']->get_active_list(0, 100);
        $this->wirteuser($userlist,'用户空间');
    }
    
    /*
	查看一个未解决问题的RSS
	rss/question/1
    */
    function onquestion() {
        $qid=$this->get[2];
        $question=$_ENV['question']->get($qid);
        $question['category_name']=$this->category[$question['cid']];
        $answerlistarray=$_ENV['answer']->list_by_qid($qid);
        $answerlist=$answerlistarray[0];
        $items=array();
        foreach($answerlist as $answer) {
            $item['id']=$answer['qid'];
            $item['title']=$question['title'];
            $item['description']=$answer['content'];
            $item['category_name']=$question['category_name'];
            $item['author']=$answer['author'];
            $item['time']=$answer['time'];
            $items[]=$item;
        }
        $this->writerss($items,$question['title'].'所有回答');
    }


    function writerss($items,$title) {
    	
        header("Content-type: application/xml");
        $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
        echo "<?xml version=\"1.0\" encoding=\"".ASK2_CHARSET."\"?>\n".
                "<rss version=\"2.0\">\n".
                "  <channel>\n".
                "    <title>".$this->setting['site_name']."</title>\n".
                "    <link>".SITE_URL."</link>\n".
                "    <description>".$title."</description>\n".
                "    <copyright>Copyright(C) ".$this->setting['site_name']."</copyright>\n".
            
                "    <lastBuildDate>".gmdate('r', $this->time)."</lastBuildDate>\n".
                "    <ttl>".$this->setting['rss_ttl']."</ttl>\n".
                "    <image>\n".
                "      <url>".SITE_URL."/css/default/logo.png</url>\n".
                "      <title>".$this->setting['site_name']."</title>\n".
                "      <link>".SITE_URL."</link>\n".
                "    </image>\n";

        foreach($items as $item) {
        	$item[description]=strip_tags(str_replace('&nbsp;','',$item['describtion']));
        	$item[title]=strip_tags(str_replace('。',',',$item['title']));
            echo "    <item>\n".
                    "      <title>".htmlspecialchars($item['title'])."</title>\n".
                    "      <link>".SITE_URL.$suffix."q-$item[id]$fix</link>\n".
                    "      <description><![CDATA[$item[description]]]></description>\n".
                    "      <category>".htmlspecialchars($item['category_name'])."</category>\n".
                    "      <author>".htmlspecialchars($item['author'])."</author>\n".
                    "      <pubDate>".@gmdate('r', $item['time'])."</pubDate>\n".
                    "    </item>\n";
        }

        echo 	"  </channel>\n".
                "</rss>";
    }
function utf8_for_xml($string)
{
    return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}
function wirteuser($items,$title){
		 header("Content-type: application/xml");
        $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
         "<urlset>\n";
            foreach($items as $item) {
            	
            	 $viewurl = urlmap('user/space/' . $item['uid'], 2);
            	
            
            	  $item[title]=$this->utf8_for_xml($item[username])."的个人空间";
            	// $item['viewtime']=tdate($item['viewtime']);
            	 $mpurl = SITE_URL . $this->setting['seo_prefix'] . $viewurl.$this->setting['seo_suffix'];
            	echo " <url>".
            	"  <loc><![CDATA[".$mpurl."]]></loc>\n".
            	
            	" <changefreq>always</changefreq>\n".
            	
            	"  <data>\n".
            	"  <display>\n";
    
            	echo "<name>".htmlspecialchars($item['title'])."</name>\n".
            	
            	" <url><![CDATA[".$mpurl."]]></url>\n";
            	
            	
            	
        
           
            	  
         
     
          	  echo " </display>\n".
          	    " </data>\n".
          	    " </url>\n";
            }
         echo "</urlset>\n";
}
function writetag($items,$title) {
      	 header("Content-type: application/xml");
        $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
         "<urlset>\n";
            foreach($items as $item) {
            	
            	 $viewurl = urlmap('tag-' . $item['name'], 2);
            	
            	 $item[title]=strip_tags(str_replace('。',',',$item['name']));
            	  $item[title]="关于".$this->utf8_for_xml($item[name])."的话题";
            	// $item['viewtime']=tdate($item['viewtime']);
            	 $mpurl = SITE_URL . $this->setting['seo_prefix'] . $viewurl.$this->setting['seo_suffix'];
            	echo " <url>".
            	"  <loc><![CDATA[".$mpurl."]]></loc>\n".
            	"  <lastmod>".$item['time']."</lastmod>\n".
            	" <changefreq>always</changefreq>\n".
            	
            	"  <data>\n".
            	"  <display>\n";
    
            	echo "<name>".htmlspecialchars($item['title'])."</name>\n".
            	
            	" <url><![CDATA[".$mpurl."]]></url>\n";
            	
            	
            	
        
           
            	  
         
     
          	  echo " </display>\n".
          	    " </data>\n".
          	    " </url>\n";
            }
         echo "</urlset>\n";
        
      }
      
 function writerss2($items,$title) {
      	 header("Content-type: application/xml");
        $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
         "<urlset>\n";
            foreach($items as $item) {
            	$item[description]=strip_tags(str_replace('&nbsp;','',$item['describtion']));
                $item[title]=strip_tags(str_replace('。',',',$item['title']));
               
               $item[title]=$this->utf8_for_xml($item[title]);
                  $item[author]=str_replace('&nbsp;', '',  $item[author]);
            	$viewurl = urlmap('question/view/' . $item['id'], 2);
            	 $mpurl = SITE_URL . $this->setting['seo_prefix'] . $viewurl.$this->setting['seo_suffix'];
            	echo " <url>".
            	"  <loc><![CDATA[".$mpurl."]]></loc>\n".
            	"  <lastmod>".@gmdate('Y-n-j H:i', $item['time'])."</lastmod>\n".
            	" <changefreq>always</changefreq>\n".
            	"  <priority>1.0</priority>\n".
            	"  <data>\n".
            	"  <display>\n";
            	 $navlist = $_ENV['category']->get_navigation($item['cid'], true);
            	 echo "<breadcrumb>\n";
            	  foreach($navlist as $nav) {
            	  	echo $nav['name']."-";
            	  }
            	echo "</breadcrumb>\n";
            	echo "<name>".htmlspecialchars($item['title'])."</name>\n".
            	 "<description><![CDATA[$item[description]]]></description>\n".
            	" <url><![CDATA[".$mpurl."]]></url>\n".
            	"<genre>站内问答</genre>\n".
            	" <provider>\n".
            	" <name>".$item['author']."</name>\n".
            	" <url>".SITE_URL.$suffix."u-$item[authorid]$fix</url>\n".
            	" </provider>\n".
            	"<collectCount>$item[attentions]</collectCount>\n".
            	"<likeCount>$item[goods]</likeCount>\n".
            	"<commentCount>$item[answers]</commentCount>\n";
            	 $taglist = $_ENV['tag']->get_by_qid($item['id']);
            	  echo "<keywords>\n";
            	 foreach($taglist as $tag) {
            	 	echo $tag.",";
            	 	
            	 }
            	  echo "</keywords>\n";
            	  
            	  echo " <downloadUrl>".$mpurl."</downloadUrl>\n".
            	  "<aggregateRating>\n".
            	  "<ratingValue>3</ratingValue>\n".
            	  "<bestRating>5</bestRating>\n".
            	  "<ratingCount>50</ratingCount>\n".
            	  " </aggregateRating>\n";
            	      $answerlistarray=$_ENV['answer']->list_by_qid($item[id]);
        $answerlist=$answerlistarray[0];
        $items=array();
        foreach($answerlist as $answer) {
        	$answer['content']=strip_tags(str_replace('&nbsp;','',$answer['content']));
        	       $answer['content']=$this->utf8_for_xml($answer['content']);
        	      $answer[author]=str_replace('&nbsp;', '',  $answer[author]);
        	echo "<comment>\n".



        //	"<commentText><![CDATA[$answer[content]]]></commentText>\n".
		"<commentText><![CDATA[".$answer['content']."]]></commentText>\n".
        	" <creator>".$answer['author']."</creator>\n".
        	"<commentTime>".$answer['time']."</commentTime>\n".
        	" </comment>\n";
        }
          	  echo " </display>\n".
          	    " </data>\n".
          	    " </url>\n";
            }
         echo "</urlset>\n";
        
      }

function writerssarticle($items,$title) {
      	 header("Content-type: application/xml");
        $suffix='?';
        if( $this->setting['seo_on']){
        	$suffix='';
        }
        $fix= $this->setting['seo_suffix'];
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
         "<urlset>\n";
            foreach($items as $item) {
            	
            	 $viewurl = urlmap('topic/getone/' . $item['id'], 2);
            	 $item[describtion]=strip_tags( str_replace('&nbsp;','',$item['describtion']));
            	 $item[title]=strip_tags(str_replace('。',',',$item['title']));
            	  $item[title]=$this->utf8_for_xml($item[title]);
            	    $item[author]=str_replace('&nbsp;', '',  $item[author]);
            	// $item['viewtime']=tdate($item['viewtime']);
            	 $mpurl = SITE_URL . $this->setting['seo_prefix'] . $viewurl.$this->setting['seo_suffix'];
            	echo " <url>".
            	"  <loc><![CDATA[".$mpurl."]]></loc>\n".
            	"  <lastmod>".$item['viewtime']."</lastmod>\n".
            	" <changefreq>always</changefreq>\n".
            	"  <priority>1.0</priority>\n".
            	"  <data>\n".
            	"  <display>\n";
            	 $navlist = $_ENV['category']->get_navigation($item['articleclassid'], true);
            	 echo "<breadcrumb>\n";
            	  foreach($navlist as $nav) {
            	  	echo $nav['name']."-";
            	  }
            	echo "</breadcrumb>\n";
            	echo "<name>".htmlspecialchars($item['title'])."</name>\n".
            	
            	" <url><![CDATA[".$mpurl."]]></url>\n".
            	"<genre>站内资讯文章</genre>\n".
            	" <provider>\n".
            	" <name>".$item['author']."</name>\n".
            	" <url>".SITE_URL.$suffix."u-$item[authorid]$fix</url>\n".
            	" </provider>\n";
            	
            	
        
           
            	  
            	  echo " <downloadUrl>".$mpurl."</downloadUrl>\n".
            	  "<aggregateRating>\n".
            	  "<ratingValue>3</ratingValue>\n".
            	  "<bestRating>5</bestRating>\n".
            	  "<ratingCount>50</ratingCount>\n".
            	  " </aggregateRating>\n";
     
          	  echo " </display>\n".
          	    " </data>\n".
          	    " </url>\n";
            }
         echo "</urlset>\n";
        
      }
}
?>