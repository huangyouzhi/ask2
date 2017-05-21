<?php

!defined('IN_ASK2') && exit('Access Denied');

class attachcontrol extends base {
var $whitelist;
    function attachcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('attach');
        
          $this->whitelist="upimg";
    }

    function onupload() {
        //上传配置
        $config = array(
            "uploadPath" => "data/attach/", //保存路径
            "fileType" => array(".rar", ".doc", ".docx", ".zip", ".pdf", ".txt", ".swf", ".wmv", "xsl"), //文件允许格式
            "fileSize" => 10 //文件大小限制，单位MB
        );

//文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜
        $state = "SUCCESS";
        $clientFile = $_FILES["upfile"];
      
        if (!isset($clientFile)) {
            echo "{'state':'文件大小超出服务器配置！','url':'null','fileType':'null'}"; //请修改php.ini中的upload_max_filesize和post_max_size
            exit;
        }

//格式验证
        $current_type = strtolower(strrchr($clientFile["name"], '.'));
        if (!in_array($current_type, $config['fileType'])) {
            $state = "不支持的文件类型！";
        }
//大小验证
        $file_size = 1024 * 1024 * $config['fileSize'];
        if ($clientFile["size"] > $file_size) {
            $state = "文件大小超出限制！";
        }
//保存文件
        if ($state == "SUCCESS") {
            $targetfile = $config['uploadPath'] . gmdate('ym', $this->time) . '/' . random(8) . strrchr($clientFile["name"], '.');
            $result = $_ENV['attach']->movetmpfile($clientFile, $targetfile);
            if (!$result) {
                $state = "文件保存失败！";
            } else {
                $_ENV['attach']->add($clientFile["name"], $current_type, $clientFile["size"], $targetfile, 0);
            }
        }
       
//向浏览器返回数据json数据
        echo '{"state":"' . $state . '","url":"' . $targetfile . '","fileType":"' . $current_type . '","original":"' . $clientFile["name"] . '"}';
    }

    function onupimg(){
    	//上传配置
        $config = array(
            "uploadPath" => "data/attach/", //保存路径
            "fileType" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp"),
            "fileSize" => 2048
        );
        
     if( $_FILES['wangEditorMobileFile']!=null){
        	$iswapeditor=true;
        	$file= $_FILES['wangEditorMobileFile'];
        }
        else{
        	 echo 'error|没上传文件';
               exit();
        }
     //文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜并显示在图片预览框，同时可以在前端页面通过回调函数获取对应字符窜
        $state = "SUCCESS";
        //格式验证
        $current_type ='jpg';// strtolower(strrchr($file["name"], '.'));
//        if (!in_array($current_type, $config['fileType'])) {
//            $state = $current_type;
//             echo 'error|图片类型不对'.$_FILES['wangEditorMobileFile'];
//               exit();
//        }
        	
       
        //大小验证
        $file_size = 1024 * $config['fileSize'];
        if ($file["size"] > $file_size) {
        	 $state = "b";
             echo 'error|图片大小不对最大支持:'.$file_size.'当前上传大小:'.$file["size"];
               exit();
        }
        	
        
       //保存图片
        if ($state == "SUCCESS") {
            $targetfile = $config['uploadPath'] . gmdate('ym', $this->time) . '/' . random(8) . strrchr($file["name"], '.');
            $result = $_ENV['attach']->movetmpfile($file, $targetfile);
            if (!$result) {
               echo 'error|上传失败';
               exit();
            } else {
                $_ENV['attach']->add($file["name"], $current_type, $file["size"], $targetfile);
            }
        }else{
        	 echo 'error|图片大小或者类型不对';
               exit();
        }
        echo SITE_URL.$targetfile;
        	exit();
    }
    function onuploadimage() {
        //上传配置
        $config = array(
            "uploadPath" => "data/attach/", //保存路径
            "fileType" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp"),
            "fileSize" => 2048
        );
        //原始文件名，表单名固定，不可配置
        $oriName =rand(11111111, 99999999);// htmlspecialchars($this->post['fileName'], ENT_QUOTES);

        //上传图片框中的描述表单名称，
        $title ="移动端图片"; //htmlspecialchars($this->post['pictitle'], ENT_QUOTES);

        //文件句柄
        $file = $_FILES["wangEditorMobileFile"];
        
        //文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜并显示在图片预览框，同时可以在前端页面通过回调函数获取对应字符窜
        $state = "SUCCESS";
        //格式验证
        $current_type = strtolower(strrchr($file["name"], '.'));
        if (!in_array($current_type, $config['fileType'])) {
            $state = $current_type;
        }
        //大小验证
        $file_size = 1024 * $config['fileSize'];
        if ($file["size"] > $file_size) {
            $state = "b";
        }
        //保存图片
        if ($state == "SUCCESS") {
            $targetfile = $config['uploadPath'] . gmdate('ym', $this->time) . '/' . random(8) . strrchr($file["name"], '.');
            $result = $_ENV['attach']->movetmpfile($file, $targetfile);
            if (!$result) {
                $state = "c";
            } else {
                $_ENV['attach']->add($file["name"], $current_type, $file["size"], $targetfile);
            }
        }
 
        if($state!="SUCCESS"){
        	echo 'error|'.$state;
        }else{
        	echo $targetfile;
        }
        //echo "{'url':'" . $targetfile . "','title':'" . $title . "','original':'" . $oriName . "','state':'" . $state . "'}";
    }

}

?>