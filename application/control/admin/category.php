<?php

!defined('IN_ASK2') && exit('Access Denied');

class admin_categorycontrol extends base {

    function admin_categorycontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('category');
    }

    function ondefault() {
   
        $category['grade'] = $pid = 0;
        $categorylist = $_ENV['category']->list_by_pid($pid);
        include template('categorylist', 'admin');
    }

    function onupdateCatTmplate(){
    	$tmpname='catlist_topic';//htmlspecialchars($this->post['tmpname']);
    		$id= intval(htmlspecialchars($this->post['id']));
    		$cat=$_ENV['category']->get($id);
    		if($cat['template']!=null&&$cat['template']!=''){
    			$tmpname='';
    		}
    	  $_ENV['category']->update_by_id_tmplate($id,$tmpname);
    	    cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
    	  echo "1";
    }
    function onpostadd(){
    	if($this->post['submit']){
    	 $pid = 0;
    	$category1 = $this->post['category1'];
            $category2 = $this->post['category2'];
           
            if (isset($category2)&&trim($category2)!='') {
                $pid = $category2;
            } else if (isset($category1)&&trim($category1)!='') {
                $pid = $category1;
            }
            $lines = explode("\n", $this->post['categorys']);
            $_ENV['category']->add($lines, $pid);
            cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
           
            exit('1');
    	}else{
    		exit('0');
    	}
    }
    function onadd() {
    	
        
            $id = intval($this->get[2]);
            $selectedarray = array();
            if ($id) {
                $category = $this->category[$id];
                $item = $category;
                for ($grade = $category['grade']; $grade > 0; $grade--) {
                    $selectedarray[] = $item['id'];
                    $item['pid'] && $item = $this->category[$item['pid']];
                }
            }
            list($category1, $category2, $category3) = array_reverse($selectedarray);
            $categoryjs = $_ENV['category']->get_js();
            include template('addcategory', 'admin');
        
    }
    //获取分类描述
    function ongetmiaosu(){
    		$id= intval(htmlspecialchars($this->post['id']));
    		  $category = $this->category[$id];
    		  echo $category['miaosu'];
    		  exit();
    }
 function oneditalias(){
    	$alias=htmlspecialchars($this->post['alias']);
    		$id= intval(htmlspecialchars($this->post['id']));
    	  $_ENV['category']->update_by_id_alias($id, $alias);
    	    cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
    	  echo "更新成功";
    }
 function oneditmiaosu(){
    	    $miaosu=$this->post['miaosu'];
          
    		$id= intval($this->post['id']);
    		 // runlog('miaosu.txt', $id.'----.'.$miaosu);
    	  $_ENV['category']->update_by_id_miaosu($id, $miaosu);
    	    cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
    	  echo "更新成功";
    }
    function onedit() {
        $id = (isset($this->get[2])) ? $this->get[2] : $this->post['id'];
        if (isset($this->post['submit'])) {
            $name = trim($this->post['name']);
            $categorydir = '';
            $cid = 0;
            $category1 = $this->post['category1'];
            $category2 = $this->post['category2'];
            $category3 = $this->post['category3'];
            if ($category3) {
                $cid = $category3;
            } else if ($category2) {
                $cid = $category2;
            } else if ($category1) {
                $cid = $category1;
            }
            $_ENV['category']->update_by_id($id, $name, $categorydir, $cid);
            cleardir(ASK2_ROOT . '/data/cache'); //清除缓存文件
            $this->post['cid'] ? $this->onview($this->post['cid']) : $this->ondefault();
        } else {
            $category = $this->category[$id];
            $item = $category;
            $selectedarray = array();
            for ($grade = $category['grade']; $grade > 1; $grade--) {
                $selectedarray[] = $item['pid'];
                $item = $this->category[$item['pid']];
            }
            list($category1, $category2, $category3) = array_reverse($selectedarray);
            $categoryjs = $_ENV['category']->get_js();
            include template('editcategory', 'admin');
        }
    }

    //后台分类管理查看一个分类
    function onview($cid = 0, $msg = '') {
        $cid = $cid ? $cid : intval($this->get[2]);
        $navlist = $_ENV['category']->get_navigation($cid); //获取导航
        $category = $this->category[$cid];
        $categorylist = $_ENV['category']->list_by_cid_pid($cid, $category['pid']); //获取子分类
        $pid = $cid;
        $msg && $message = $msg;
        include template('categorylist', 'admin');
    }

    //删除分类
    function onremove() {
        if (isset($this->post['cid'])) {
            $cids = implode(",", $this->post['cid']);
            $pid = intval($this->post['hiddencid']);
            $_ENV['category']->remove($cids);
            $this->onview($pid, '分类删除成功!');
        }
    }

    /* 后台分类排序 */

    function onreorder() {
        $orders = explode(",", $this->post['order']);
        foreach ($orders as $order => $cid) {
            $_ENV['category']->order_category(intval($cid), $order);
        }
        $this->cache->remove('category');
    }
    //修改封面图
    function oneditimg(){
     if (isset($_FILES["catimage"])) {
            $uid = intval($this->post['catid']);
        
          
            $avatardir = "/data/category/";
            $extname = extname($_FILES["catimage"]["name"]);
            if (!isimage($extname))
                $this->message("图片扩展名不正确!", 'admin_category/editimg');
            $upload_tmp_file = ASK2_ROOT . '/data/tmp/cat_' . $uid . '.' . $extname;
            $uid = abs($uid);
            $uid = sprintf("%09d", $uid);
            $dir1 = $avatardir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 2);
            $dir3 = $dir2 . '/' . substr($uid, 5, 2);
            (!is_dir(ASK2_ROOT . $dir1)) && forcemkdir(ASK2_ROOT . $dir1);
            (!is_dir(ASK2_ROOT . $dir2)) && forcemkdir(ASK2_ROOT . $dir2);
            (!is_dir(ASK2_ROOT . $dir3)) && forcemkdir(ASK2_ROOT . $dir3);
            $bigimg = $dir3 . "/big_" . $uid . '.' . $extname;
            $smallimg = $dir3 . "/small_" . $uid . '.' . $extname;
                 
            if (move_uploaded_file($_FILES["catimage"]["tmp_name"], $upload_tmp_file)) {
           
                $avatar_dir = glob(ASK2_ROOT . $dir3 . "/small_{$uid}.*");
              
                foreach ($avatar_dir as $imgfile) {
                	 
                    if (strtolower($extname) != extname($imgfile))
                        unlink($imgfile);
                }
               
                 
                     image_resize($upload_tmp_file, ASK2_ROOT . $bigimg, 195, 195,1);
                     
                 
               image_resize($upload_tmp_file, ASK2_ROOT . $smallimg, 32, 32,1);

              
             
            }
            
          
        }
            header("Location:" . SITE_URL.'index.php?admin_category.html');
    }

}

?>