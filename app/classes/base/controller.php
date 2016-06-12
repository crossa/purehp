<?php

namespace base;

class Controller {
	
	public $isApp = false;
	public function __construct() {
		if ("yes" == $_REQUEST ['app'])
			$this->isApp = true;
		
		if($this->isApp){
			$data  = file_get_contents("php://input","r");
			$_POST=empty($data)?array():$this->umask($data);
		}
	}
	
	public function createToken($id){
		$token = array();
		$token['uid'] = $id;
		$token['expire'] = time()+3600*24*90;
		$str = json_encode($token);
		$result['token'] = base64_encode($this->mask(str));
		$result['secret'] = trans($result['token']);
	}
	
	public function needLogin(){
		if($this->isApp)
			$this->checkAppLogin();
		else
			$this->checkNormalLogin();
	}
	
	

	public function checkNormalLogin(){
		if(empty($_SESSION['islogin']) || empty($_SESSION['member']))
			$this->send(\model\Message::NOT_LOGIN, "需要登录");
	}
	
	
	public function checkAppLogin(){
		if(empty($_POST['token']))
			$this->send(\model\Message::NOT_LOGIN, "需要登录");
		
		$token = $this->umask($_POST['token']);
		if(empty($token))
			$this->send(\model\Message::NOT_LOGIN, "需要登录");
			
		if($token['expire']<time())
			$this->send(\model\Message::NOT_LOGIN, "需要登录");
		
		if(class_exists("\model\Member")){
			$model = new \model\Member("database/istock");
			if(!$model->checkToken($_POST['token']))
				$this->send(\model\Message::NOT_LOGIN, "需要登录");
		}
	}
	
	
	
	
	public function quickPost($url, $param,$h=null) {
		$handle = curl_init (trim($url ));
		$header = array();
		$header[] = 'Expect:';
		$header[] = "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0";
		$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		//$header = array();
		if(!empty($h)){
			$header = array_merge($header,$h);
		}
		curl_setopt($handle, CURLOPT_HTTPHEADER,$header);
		curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $handle, CURLOPT_POST, true );
		curl_setopt ( $handle, CURLOPT_POSTFIELDS, $param );
		$result = curl_exec ( $handle );
		if (curl_errno($handle)) {
			print "Error: " . curl_error($handle);
		}
		curl_close ( $handle );
		return $result;
	}
	
	
	public function send($code, $msg, $data=array()) {
		if ($this->isApp) {
			$this->sendEncrypted($code, $msg,$data);
		}
		$this->sendDefault ( $code, $msg, $data );
	}
	
	public function sendEncrypted($code, $msg,$data=array()) {
		if (is_array ( $code )) {
			$result ['code'] = 0;
			$result ['info'] = "正常";
			$result ['data'] = $code;
		} else {
			$result ['code'] = $code;
			$result ['info'] = $msg;
			$result ['data'] = $data;
		}
		$str = json_encode($result);
		$config = $this->config("token/default");
		$str = openssl_private_encrypt($str, $return, file_get_contents($config['locker']));
		exit(base64_encode($str));
	}

	public function sendDefault($code, $msg, $data = array()) {
		$result = array ();
		if (is_array ( $code )) {
			$result ['code'] = 0;
			$result ['info'] = "正常";
			$result ['data'] = $code;
			exit ( json_encode ( $result ) );
		}
		$result ['code'] = $code;
		$result ['info'] = $msg;
		$result ['data'] = $data;
		exit ( json_encode ( $result ) );
	}
	
	/**
	 * alias of send
	 * 
	 * @param unknown $code        	
	 * @param unknown $msg        	
	 * @param unknown $data        	
	 */
	public function exitWithJson($code, $msg, $data = array()) {
		$this->send ( $code, $msg, $data );
	}
	
	/**
	 * 签名动作
	 * 
	 * @param unknown $param        	
	 * @param unknown $token        	
	 * @param unknown $result        	
	 */
	public function sign($param, $token) {
		$config = $this->config ( "token/default" );
		ksort ( $param );
		$str = http_build_query ( $param );
		$str .= "&" . $token;
		$handle = openssl_pkey_get_private ( file_get_contents ( $config ['locker'] ) );
		openssl_sign ( $str, $result, $handle );
		openssl_free_key ( $handle );
		return $result;
	}
	
	/**
	 */
	public function verify($param, $token) {
		$sign = $param ['sign'];
		unset ( $param ['sign'] );
		$config = $this->config ( "token/default" );
		ksort ( $param );
		$str = http_build_query ( $param );
		$str .= "&" . $token;
		$handle = openssl_get_publickey ( file_get_contents ( $config ['key'] ) );
		$result = openssl_verify ( $str, $sign, $handle );
		openssl_free_key ( $handle );
		return $reuslt;
	}
	
	/**
	 * 遮盖
	 * 
	 * @param unknown $param        	
	 */
	public function mask($param) {
		$config = $this->config ( "token/default" );
		$param = json_encode ( $param );
		openssl_public_encrypt ( $param, $result, file_get_contents ( $config ['key'] ) );
		return base64_encode ( $result );
	}
	
	/**
	 * 解除
	 * 
	 * @param unknown $param        	
	 */
	public function umask($param) {
		$param = base64_decode($param);
		$config = $this->config ( "token/default" );
		openssl_private_decrypt ( $param, $result, file_get_contents ( $config ['locker'] ) );
		return json_decode($result,true);
	}
	
	/**
	 * 读取配置文件
	 * 
	 * @param unknown $config        	
	 */
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
	
	/**
	 * 跳转到指定的类与方法
	 */
	public function redirect($path,$method,$param=null,$host=null){
		$protocol = "http://";
		$host = empty($host)?$_SERVER['HTTP_HOST']:$host;
		$query = http_build_query($param);
		$url = "{$protocol}{$host}{$path}/{$method}.json";
		if(!empty($url))
			$url.="?{$query}";
		header("Location: $url");
		exit();
	}
	
	public function makeUrl($path,$method,$param=null,$host=null){
		$protocol = "http://";
		$host = empty($host)?$_SERVER['HTTP_HOST']:$host;
		$query = http_build_query($param);
		$url = "{$protocol}{$host}{$path}/{$method}.json";
		if(!empty($param))
			$url.="?{$query}";
		return $url;
	}
	
	public function getUid(){
		if(!$this->isApp){
			return $_SESSION['member']['id'];
		}
	}
	
	
	
}
?>