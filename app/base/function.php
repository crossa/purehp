<?php
/**
 * File: function.php 
 * Date: 2016/1/4
 * Time: 16:32
 */

/**
 * 数字格式化
 */
function ncPriceFormat($price, $round = 2, $splite = ".") {
	$price_format = number_format ( $price, $round, $splite, '' );
	return $price_format;
}

/**
 *
 * @param unknown $classname        	
 */
function __loadme($classname) {
	if (class_exists ( $classname, false ))
		return;
	$f = strtolower ( str_replace ( "\\", "/", $classname ) );
	$file = APP . "/classes/${f}.php";
	if (file_exists ( $file )) {
		require_once ($file);
		return;
	}
}
/**
 * 清理Opache
 */
function __clearOpcache() {
	if (! empty ( $_REQUEST ['reset_opcache'] )) {
		if (function_exists ( "opcache_reset" )) {
			opcache_reset ();
		}
	}
}

/**
 *
 * @param        	
 *
 */
function __library() {
	require_once (APP . "/base/front.php");
	require_once (APP . "/base/Common.php");
	require_once (APP . "/base/modelmultimysql.php");
}

/**
 * 程序开始运行
 */
function __run() {
	global $argc,$argv;
	spl_autoload_register ( "__loadme" );
	$env = php_sapi_name ();
	if ($env == 'cli') {
		$r = $argv [1];
	} else {
		$r = trim ( $_GET ['r'] );
	}
	if (empty ( $r )) {
		if (defined ( "INDEX" )) {
			$r = INDEX;
		}
	}
	$r = str_replace ( "/", "\\", $r );
	$arr = explode ( "\\", $r );
	$arr = array_diff ( $arr, array (
			null 
	) );
	$r = implode ( "\\", $arr );
	$arr = explode ( "\\", $r );
	if(1==sizeof($arr)){
		$arr[]="index";
	}
	$action = $arr [count ( $arr ) - 1];
	array_pop ( $arr );
	$arr [count ( $arr ) - 1] = ucfirst ( $arr [count ( $arr ) - 1] );
	$class = "\\" . implode ( "\\", $arr );
	if(!class_exists($class)){
		header ( "HTTP/1.0 404 Not Found" );
		return false;
	}
	$obj = new $class ();
	$obj->__action =  $action;
	if (method_exists ( $obj, "$action" )) {
		$obj->$action ();
		return true;
	}
	exit(json_encode(array("code"=>-1,"info"=>"调用了不存在的接口")));
}


function quickPost($url, $param,$h=null) {
	$handle = curl_init ( $url );
	$header = array('Expect:');
	$header = array();
	if(!empty($h)){
		$header = array_merge($header,$h);
	}
	curl_setopt($handle, CURLOPT_HTTPHEADER,$header);
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $param );
	$result = curl_exec ( $handle );
	curl_close ( $handle );
	return $result;
}


 function md5ToInt($hash) {
	$n = 0;
	for($i = 0; $i < 4; $i ++) {
		$str = substr ( $hash, $i * 2, 2 );
		$x = intval ( $str, 16 );
		$n = $n << 8;
		$n |= $x;
	}
	return abs ( $n );
}

function trans($str){
	return md5ToInt(md5($str));
}



?>