<?php
error_reporting ( E_ALL & ~ E_WARNING & ~ E_NOTICE & ~ E_DEPRECATED & ~ E_STRICT );
header ( "Access-Control-Allow-Origin: *" );

if(!empty($_GET['echostr'])){
	echo $_GET['echostr'];
	exit();
}

// 基本
require_once (dirname ( __FILE__ ) . "/front_config.php");
require_once (APP . "/base/function.php");

/* 初始化连接 */
$_connections = array ();
$_authenticated = array ();

__clearOpcache ();
__library ();

/**
 * php5 MONGO 驱动
 */
if (class_exists ( "MongoCollection" )) {
	require_once (APP . "/base/modelmultimongo.php");
}

/**
 * php5／7 都可以用的驱动
 */
if (class_exists ( "MongoDB\Driver\Manager" )) {
	require_once (APP . "/base/modelmongov2.php");
}

if (is_php ( '5.4.0' ) && class_exists ( "MongoCollection" )) {
	require_once (APP . "/base/mongosession.php");
}
require_once (APP . "/base/session.php");
date_default_timezone_set ( "Asia/Shanghai" );

__run ();

?>
