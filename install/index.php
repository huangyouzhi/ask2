<?php

/*
[Tipask!] (C)2010 - 2014  Tipask Inc.
This is NOT a freeware, use is subject to license terms

$Id: index.php 2009-12-22 11:15 Cral $
*/

error_reporting(0);
@set_time_limit(1000);
@set_magic_quotes_runtime(0);

define('IN_ASK2', TRUE);
require 'var.inc.php';
require ASK2_ROOT.'./install/install.lang.php';
require ASK2_ROOT.'./install/func.inc.php';
$site_url="http://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-17);
$view_off = getgpc('view_off');

define('VIEW_OFF', $view_off ? TRUE : FALSE);

$allow_method = array('show_license', 'env_check', 'db_init', 'ext_info','post_siteinfo', 'install_check', 'tablepre_check');

$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');


if(empty($method) || !in_array($method, $allow_method)) {
	$method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if(empty($method)) {
	show_msg('method_undefined', $method, 0);
}

if(!ini_get('short_open_tag')) {
	show_msg('short_open_tag_invalid', '', 0);
} elseif(file_exists($lockfile)) {
	show_msg('install_locked', '', 0);
}  


 
if($method == 'show_license') {

	show_license();

} elseif($method == 'env_check') {
	
	VIEW_OFF && function_check($func_items);

	env_check($env_items);

	dirfile_check($dirfile_items);

	show_env_result($env_items, $dirfile_items, $func_items);

} elseif($method == 'db_init') {

	@include CONFIG;
	$submit = true;
	$error_msg = array();

	if(isset($form_db_init_items) && is_array($form_db_init_items)) {
		foreach($form_db_init_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = $tmp[$k];
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}
	
	
	if(!VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if($ucfounderpw != $ucfounderpw2) {
			$error_msg['admininfo']['ucfounderpw2'] = 1;
			$submit = false;
		}

		$forceinstall = isset($_POST['dbinfo']['forceinstall']) ? $_POST['dbinfo']['forceinstall'] : '';
		$dbname_not_exists = true;
		if(!empty($dbhost) && empty($forceinstall)) {
			$dbname_not_exists = check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre);
			if(!$dbname_not_exists) {
				$form_db_init_items['dbinfo']['forceinstall'] = array('type' => 'checkbox', 'required' => 0, 'reg' => '/^.*+/');
				$error_msg['dbinfo']['forceinstall'] = 1;
				$submit = false;
				$dbname_not_exists = false;
			}
		}
	}

	if($submit) {
        
		$step = $step + 1;
		if(empty($dbname)) {
			show_msg('dbname_invalid', $dbname, 0);
		} else {
			if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
				$errno = mysql_errno();
				$error = mysql_error();
				if($errno == 1045) {
					show_msg('database_errno_1045', $error, 0);
				} elseif($errno == 2003) {
					show_msg('database_errno_2003', $error, 0);
				} else {
					show_msg('database_connect_error', $error, 0);
				}
			}
			if(mysql_get_server_info() > '4.1') {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET ".DBCHARSET);
			} else {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`");
			}

			if(mysql_errno()) {
				show_msg('database_errno_1044', mysql_error(), 0);
			}
			mysql_close();
		}

		if(strpos($tablepre, '.') !== false || intval($tablepre{0})) {
			show_msg('tablepre_invalid', $tablepre, 0);
		}

		config_edit();
		require ASK2_ROOT.'./lib/db.class.php';
		$db = new db($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);
		$sql = file_get_contents($sqlfile);
		$sql = str_replace("\r\n", "\n", $sql);

		if(!VIEW_OFF) {
			show_header();
			show_install();
		}

		runquery($sql);
		//add default user
		
		$db->query("INSERT INTO `".$tablepre."user` SET `username`='".$admininfo['ucadminname']."', `password`='".md5(trim($admininfo['ucfounderpw']))."',`email`='".$admininfo['ucadminemail']."',`groupid`=1,`credits`=200,`credit1`=100,`credit2`=100,`regip`='".$_SERVER["REMOTE_ADDR"]."'");

		$db->query("INSERT INTO `".$tablepre."setting` SET `k`='auth_key',`v`='".generate_key()."'");

		$db->query("INSERT INTO `".$tablepre."setting` SET `k`='admin_email',`v`='".$admininfo['ucadminemail']."'");
		$db->query("INSERT INTO `".$tablepre."setting` SET `k`='admin_email',`v`='".$admininfo['ucadminemail']."'");
		
		dir_clear(ASK2_ROOT.'./data/cache');//clear up the old data cathe
		VIEW_OFF && show_msg('initdbresult_succ');

		if(!VIEW_OFF) {
			echo '<script type="text/javascript">document.getElementById("step3").disabled=false;document.getElementById("step3").value = \''.lang('install_succeed').'\';</script>'."\r\n";
			show_footer();
		}

	}
	if(VIEW_OFF) {

		show_msg('missing_parameter', '', 0);

	} else {
		show_form($form_db_init_items, $error_msg);

	}

} elseif($method == 'post_siteinfo'){
		header('location:../index.php');
	$infoarr = array(
		'site_url'=>$site_url,
		'domain'=>$_SERVER['HTTP_HOST'],
		'version'=>ASK2_VERSION
	);
	$info = urlencode(base64_encode(serialize($infoarr)));
	@touch($lockfile);
	show_header();
	//echo '<iframe frameborder="0" src="http://www.tipask.com/site/index.php?siteinfo/add/'.$info.'.html" width="650" height="350"></iframe>';
	show_footer();
}elseif($method == 'ext_info') {

	//@touch($lockfile);
  	header('location:../index.php');
 

} elseif($method == 'install_check') {

	if(file_exists($lockfile)) {
		@touch(ASK2_ROOT.'./data/install.lock');
		show_msg('installstate_succ');
	} else {
		show_msg('lock_file_not_touch', $lockfile, 0);
	}

} elseif($method == 'tablepre_check') {

	$dbinfo = getgpc('dbinfo');
	extract($dbinfo);
	if(check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
		show_msg('tablepre_not_exists', 0);
	} else {
		show_msg('tablepre_exists', $tablepre, 0);
	}
}