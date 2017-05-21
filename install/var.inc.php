<?php

/*
	[Ask2!] (C)2001-2009 ASK2 Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: var.inc.php 2009-3-22 11:30 Cral $
*/

if(!defined('IN_ASK2')) {
	exit('Access Denied');
}

define('ASK2_VERSION', '3.5');
define('ASK2_RELEASE', '20170521');
define('INSTALL_LANG', 'SC_UTF8');
define('ASK2_ROOT', dirname(__FILE__).'/../');
define('CONFIG', ASK2_ROOT.'./config.php');
$sqlfile = ASK2_ROOT.'./install/ask2.sql';
$lockfile = ASK2_ROOT.'./data/install.lock';

define('CHARSET', 'UTF-8');
define('DBCHARSET', 'utf8');

define('ORIG_TABLEPRE', 'ask_');

define('METHOD_UNDEFINED', 255);
define('ENV_CHECK_RIGHT', 0);
define('ERROR_CONFIG_VARS', 1);
define('SHORT_OPEN_TAG_INVALID', 2);
define('INSTALL_LOCKED', 3);
define('DATABASE_NONEXISTENCE', 4);
define('PHP_VERSION_TOO_LOW', 5);
define('MYSQL_VERSION_TOO_LOW', 6);
define('ask_URL_INVALID', 7);
define('ask_DNS_ERROR', 8);
define('ask_URL_UNREACHABLE', 9);
define('ask_VERSION_INCORRECT', 10);
define('ask_DBCHARSET_INCORRECT', 11);
define('ask_API_ADD_APP_ERROR', 12);
define('ask_ADMIN_INVALID', 13);
define('ask_DATA_INVALID', 14);
define('DBNAME_INVALID', 15);
define('DATABASE_ERRNO_2003', 16);
define('DATABASE_ERRNO_1044', 17);
define('DATABASE_ERRNO_1045', 18);
define('DATABASE_CONNECT_ERROR', 19);
define('TABLEPRE_INVALID', 20);
define('CONFIG_UNWRITEABLE', 21);
define('ADMIN_USERNAME_INVALID', 22);
define('ADMIN_EMAIL_INVALID', 25);
define('ADMIN_EXIST_PASSWORD_ERROR', 26);
define('ADMININFO_INVALID', 27);
define('LOCKFILE_NO_EXISTS', 28);
define('TABLEPRE_EXISTS', 29);
define('ERROR_UNKNOW_TYPE', 30);
define('ENV_CHECK_ERROR', 31);
define('UNDEFINE_FUNC', 32);
define('MISSING_PARAMETER', 33);
define('LOCK_FILE_NOT_TOUCH', 34);

$func_items = array('mysql_connect', 'fsockopen', 'gethostbyname', 'file_get_contents', 'xml_parser_create');

$env_items = array
(
	'os' => array('c' => 'PHP_OS', 'r' => 'notset', 'b' => 'unix'),
	'php' => array('c' => 'PHP_VERSION', 'r' => '4.0', 'b' => '5.0-5.6'),
	'attachmentupload' => array('r' => 'notset', 'b' => '2M'),
	'gdversion' => array('r' => '1.0', 'b' => '2.0'),
	'diskspace' => array('r' => '10M', 'b' => 'notset'),
);

$dirfile_items = array
(
	'config' => array('type' => 'file', 'path' => './config.php'),
	'data' => array('type' => 'dir', 'path' => './data'),
	'category' => array('type' => 'dir', 'path' => './category'),
	'cache' => array('type' => 'dir', 'path' => './data/cache'),
	'view' => array('type' => 'dir', 'path' => './data/view'),
	'avatar' => array('type' => 'dir', 'path' => './data/avatar'),
	'logs' => array('type' => 'dir', 'path' => './data/logs'),
	'backup' => array('type' => 'dir', 'path' => './data/backup'),
	'attach' => array('type' => 'dir', 'path' => './data/attach'),
	'logo' => array('type' => 'dir', 'path' => './data/attach/logo'),
		'banner' => array('type' => 'dir', 'path' => './data/attach/banner'),
		'topic' => array('type' => 'dir', 'path' => './data/attach/topic'),
	'upload' => array('type' => 'dir', 'path' => './data/upload'),
	'ueditor' => array('type' => 'dir', 'path' => './data/ueditor'),
	'tmp' => array('type' => 'dir', 'path' => './data/tmp')
);

$form_db_init_items = array
(
	'dbinfo' => array
	(
		'dbhost' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'localhost')),
		'dbname' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'ask2')),
		'dbuser' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'root')),
		'dbpw' => array('type' => 'password', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => '')),
		'tablepre' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'ask_')),
	),
	'admininfo' => array
	(
		'ucadminname'=>array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'admin')),
		'ucfounderpw' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'ucfounderpw2' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'ucadminemail'=>array('type' => 'text', 'required' => 1, 'reg' => "/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/",  'value' => array('type' => 'string', 'var' => 'webmaster@domain.com'))
	)
);