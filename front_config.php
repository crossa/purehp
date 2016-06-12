<?php
define("WEBROOT",dirname(__FILE__));
define("APPPATH","/".basename(dirname(__FILE__)));
define("JSON",WEBROOT."/json");
define("APP",WEBROOT."/app");
define("CONFIG",WEBROOT."/app/config");
define("VIEW",APP."/v");
define("MEMCACHE",0);
define("SESSION",0);
define("HOST",$_SERVER['HTTP_HOST']);
define("DOMAIN",$_SERVER['SERVER_NAME']);
define("SALT","caiku123");
/*
define("IMG_HOST","www.vyishu.com/upload");//图片地址
if(MEMCACHE){
	define("MEMCACHE_HOST","localhost");
}*/
define("DEBUG",true);
define("IMAGE_DIR",WEBROOT."/proof/images");
define("THUMB_DIR","/srv/upload/images/thumb");
define("TEMP_DIR","/srv/upload/temp");
define("TEMP_THUMB_DIR","/srv/upload/temp_thumb");

define("XPROF",0);
define("DCONFIG",APP."/save");
define("SITE","http://devcn.xinyingbao.com/uploads");   //不可删
define("IMAGE_SITE","http://img.rank.com");
define("COOKIENAME","chat_cookie");
define("SESSIONNAME","chat_session");

define("IMG_URL","http://img.nczmall.com/shop/store/goods/");//图片的访问地址，后接gridfs里的图片id
define("WEB_URL","http://dev.xinyingbao.com/api/web");//web的地址
define('IS_WX',strpos($_SERVER['HTTP_USER_AGENT'],"MicroMessenger"));
define("TEMP_BOOT","http://www.517dv.com/temp");//不可删
define("TEMPROOT",dirname(__FILE__)."/temp");		//不可删
define("UPLOADROOT",dirname(__FILE__)."/uploads");	//不可删

define('ATTACH_BRAND', 'shop/brand');//品牌图片位置
define('UPLOAD_SITE_URL','http://img.nczmall.com');//图片地址
define('ATTACH_COMMON', 'shop/common');//公共图片位置
define('TIMESTAMP',time());
define('GOODS_IMAGES_EXT', '_60,_240,_360,_1280');
define('ATTACH_GOODS', 'shop/store/goods');
define('BASE_ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)));
define('BASE_DATA_PATH', BASE_ROOT_PATH . '/data');
define('BASE_UPLOAD_PATH', BASE_DATA_PATH . '/upload');
define('DS', '/');
define('UPLOAD_MY_URL', 'http://dev.nczmall.com/upload/');

//新城猫路径
if("dev.nczmall.com"==$_SERVER['HTTP_HOST']){
	define('NCZMALL_UPLOAD_DIR',"/data/www/nczmall/data/upload");
	define('NCZMALL_IMAGE_URL',"http://dev.nczmall.com/upload");
}else{
	define('NCZMALL_UPLOAD_DIR',"/image/upload");
	define('NCZMALL_IMAGE_URL',"http://img.nczmall.com");
}

//新城猫后台用上传路径
if("admindev.nczmall.com"==$_SERVER['HTTP_HOST']){
	define('NCZMALL_ADMIN_UPLOAD_DIR',"/data/www/nczmall/data/upload");
	define('NCZMALL_ADMIN_IMAGE_URL',"http://dev.nczmall.com/upload");
}else{
	define('NCZMALL_ADMIN_UPLOAD_DIR',"/data/www/nczmall_b2b2c/data/upload");
	define('NCZMALL_ADMIN_IMAGE_URL',"http://img.nczmall.com");
}











?>
