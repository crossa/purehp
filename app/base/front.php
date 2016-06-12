<?php
use tool\Debug as DBG;
class FrontBase {
	public $mem;
	public $sys;
	public $root;
	public $handle;
	public $__action;
	protected $libraries;
	public $get;
	public function setHandle($obj) {
		$this->handle = $obj;
	}
	public function getHandle() {
		return $this->handle;
	}
	protected function notfound() {
		header ( "HTTP/1.0 404 Not Found" );
		die ();
	}
	public function __construct() {
		//$this->libraries = new stdClass ();
		//$this->fetch ();
		//$this->root = "/" . $this->webroot ();
	}
	protected function token($sid) {
		return false;
	}

	/**
	 * 生成文件
	 *
	 * @param string $view
	 *        	生成页面的原始页面
	 * @param array $param
	 *        	页面需要传入的参数
	 * @param string $path
	 *        	生成页面存放的路劲和文件件名
	 * @return boolean
	 */
	protected function savepage($view, $param, $path) {
		if (empty ( $view )) {
			return false;
		}
		if (strpos ( $view, '/' )) {
			$base_path = VIEW . "/" . "${view}.php";
		} else {
			$class = strtolower ( get_class ( $this ) );
			$base_path = VIEW . "/" . $class . "/" . "${view}.php";
		}
		if (file_exists ( $base_path )) {
			unset ( $class );
			if (! empty ( $param ))
				extract ( $param );
			ob_start ();
			require (strtolower ( $base_path ));
			$str = ob_get_contents ();
			ob_end_clean ();
			$dir = dirname ( $path );
			if (! file_exists ( $dir ))
				mkdir ( $dir, 0755, true );
			file_put_contents ( $path, $str );
			return true;
		}
		return false;
	}
	protected function render($view, $param = null) {
		if (empty ( $view ))
			return false;
		$class = strtolower ( get_class ( $this ) );
		$base_path = VIEW . "/" . $class . "/" . "${view}.php";
	 	$base_path = str_replace ( "\\", "/", $base_path );
		if (file_exists ( $base_path )) {
			if (! empty ( $param ) && is_array ( $param )) {
				extract ( $param );
				unset ( $flag );
			}
			require (strtolower ( $base_path ));
		} else {
			die ( "View Not Found" );
		}
	}
	protected function html($view, $param = null) {
		if (empty ( $view ))
			return false;
		$class = strtolower ( get_class ( $this ) );
		$base_path = VIEW . "/" . $class . "/" . "${view}.php";
		$base_path = str_replace ( "\\", "/", $base_path );
		if (file_exists ( $base_path )) {
			if (! empty ( $param ) && is_array ( $param ))
				extract ( $param );
			unset ( $flag );
			ob_start ();
			require (strtolower ( $base_path ));
			$str = ob_get_contents ();
			ob_end_clean ();
			return $str;
		}
		return false;
	}

	/**
	 * 包含页面 如公共页面 footer、header .
	 *
	 * @param string $view
	 *        	包含的页面（只能包含页面）
	 * @param array $param
	 *        	页面需要传入的变量
	 * @return boolean
	 */
	protected function include_page($view, $param = null) {
		if (! $view) {
			return false;
		}
		if (strpos ( $view, '/' )) {
			$page = VIEW . "/" . "${view}.php";
		} else {
			$class = strtolower ( get_class ( $this ) );
			$page = VIEW . "/" . $class . "/" . "${view}.php";
		}
		if (file_exists ( $page )) {
			if (! empty ( $param ) && is_array ( $param )) {
				extract ( $param );
				unset ( $flag );
			}
			require (strtolower ( $page ));
		} else {
			die ( "This Page Is Not Found" );
		}
	}
	protected function cookie($name, $value = null, $life = null) {
		if ($life == - 1) {
			return setcookie ( $name, $value, time () - 1, "/", DOMAIN, false, false );
		} else {
			$left = 24 * 3600;
		}

		if (empty ( $value )) {
			return $_COOKIE [$name];
		}
		$_COOKIE [$name] = $value;
		return setcookie ( $name, $value, time () + $life, "/", DOMAIN, false, false );
	}
	protected function redirect($url) {
		echo "<script type='text/javascript'>";
		echo "window.location.href='${url}';";
		echo "</script>";
		die ();
	}
	
	protected function config($config) {
		$file = CONFIG . "/$config.php";
		$tmp = explode ( "/", $config );
		$tmp = array_diff ( $tmp, array (
				null
		) );
		if (count ( $tmp ) == 0) {
			return null;
		}
		if (file_exists ( $file )) {
			require ($file);
			return $config;
		}
		require (CONFIG . "/" . $tmp [0] . "/default.php");
		return $config;
	}


	protected function getHyperlink($str) {
		if (empty ( $str ))
			return false;
		$exp = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		preg_match ( $exp, $str, $out );
		return $exp [0];
	}
	protected function fetch() {
		$url = $_SERVER ['REQUEST_URI'];
		$string = parse_url ( $url );
		$query = $string ['query'];
		$tmp = explode ( "&", $query );
		if (! empty ( $tmp )) {
			foreach ( $tmp as $q ) {
				$s = explode ( "=", $q );
				$data [$s [0]] = $s [1];
			}
		}
		$_GET = $data;
		$this->get = $data;
	}
	
	protected function showStatic($file) {
		if (file_exists ( $file ))
			require_once ($file);
	}

	protected function webroot() {
		$uri = $_SERVER ['REQUEST_URI'];
		$uri = explode ( "/", $uri );
		$uri = array_diff ( $uri, array (
				null
		) );
		if (empty ( $uri ))
			return "/";
		$uri = array_slice ( $uri, 0, 1 );
		return $uri [0];
	}
	
	protected function library($classname, $param) {
		$f = strtolower ( $classname );
		$file = APP . "/module/${f}.php";

		if (! empty ( $this->libraries->$f )) {
			return $this->libraries->$f;
		}

		if (class_exists ( $classname )) {
			$this->libraries->$f = new $classname ( $param );
			return $this->libraries->$f;
		}

		if (file_exists ( $file )) {
			require_once ($file);
			$this->libraries->$f = new $classname ( $param );
			return $this->libraries->$f;
		}
		return false;
	}

	
	
	/**
	 * 1.小于60分钟，则显示**分钟前
	 * （例：1分钟前、59分钟前）
	 * 2.大于等于60分钟且小于24小时，则显示**小时前（只显示小时数，分钟忽略）
	 * （例：61分钟前发帖的显示1小时前，2小时57分钟的显示2小时前，23小时59分钟的显示23小时前）
	 * 3.大于等于24小时，小于24*2=48小时，则显示1天前、（只显示天数，小时、分钟忽略）
	 * 大于等于48小时，小于24*3=72小时，则显示2天前、
	 * 大于等于72小时，小于24*4=96小时，则显示3天前
	 * 4.大于等于96小时，直接显示日期
	 * （例:现在是29日13:20，发帖日是25日9:04，则显示6月25日）
	 */
	public function show_time($time) {
		$time = intval ( $time );
		$t = floor ( (strtotime ( "now" ) - $time) / 60 );
		if(0==$t){
			return "刚刚";
		}

		if ($t < 60 && $t > 0) {
			return $t . "分钟前";
		}
		if ($t >= 60 && $t < 60 * 24) {
			return floor ( $t / 60 ) . "小时前";
		}
		if ($t >= 60 * 24 && $t < 60 * 24 * 365) {
			return floor ( $t / 60 / 24 ) . "天前";
		}

		if ($t < 60 * 24 * 365 * 100 && $t >= 60 * 24 * 365) {
			return floor ( $t / 60 / 24 / 365 ) . "年前";
		}
		return "N年前";
	}


	public function generateToken($uid,$key){
		return md5(sha1($uid).sha1($key).strtotime("now"));
	}
	
	
	public function getPath($obj){
		return str_replace("\\","/",get_class($obj));
	}

}

?>
