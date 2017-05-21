<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_templatecontrol extends base {

    function admin_templatecontrol(& $get, & $post) {
        $this->base($get, $post);
          $this->load('setting');
    }
      function ondefault($message='', $type='correctmsg') {
      	if($this->get[2]=='wap'){
      		include template('tmp_wap', 'admin');
      	}else{
      		include template('tmp_pc', 'admin');
      	}
      	 
      }
      
  
       function oneditdirfile(){
       	if($this->post['submit']){
       		
       		$message="模板编辑成功!";
       		 $dir= $this->post['dir'];
       $dir_file=$this->post['dir_file'];
       chmod("view/".$dir, 0777); 
       		  file_put_contents(ASK2_APP_ROOT."view/".$dir."/".$dir_file.".html",stripslashes(htmlspecialchars_decode($this->post['tpl_content'])));
       		  
       $tpl_content=file_get_contents(ASK2_APP_ROOT."view/".$dir."/".$dir_file.".html");
       	}else{
       		 $dir= $this->get[2];
       $dir_file=$this->get[3];
       
       $tpl_content=htmlspecialchars(file_get_contents(ASK2_APP_ROOT."view/".$dir."/".$dir_file.".html"));
        
     
       	}
      
       include template('tmp_editfile', 'admin');
       }
       function ongetpcdir(){
         	  $tpllist = $_ENV['setting']->tpl_list();
         	  $tppclist=implode(',', $tpllist);
         	  echo $tppclist;
       }
 function ongetwapdir(){
         	  $tpllist = $_ENV['setting']->tpl_waplist();
         	  $tppclist=implode(',', $tpllist);
         	  echo $tppclist;
       }
        function ongetpcdirfile(){
        	$dir=$this->post['dirname'];
         	  	$file_dir=ASK2_APP_ROOT."view/".$dir;
         	  
         	 
include $file_dir.'/'.$dir.'.php';
echo json_encode($tphtml);
       }

       
      
}