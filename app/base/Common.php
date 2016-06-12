<?php

/**
 * Determines if the current version of PHP is greater then the supplied value
 *
 * Since there are a few places where we conditionally test for PHP > 5
 * we'll set a static variable.
 *
 * @access	public
 * @param	string
 * @return	bool	TRUE if the current version is $version or higher
 */
if (! function_exists ( 'is_php' )) {
	function is_php($version = '5.0.0') {
		static $_is_php;
		$version = ( string ) $version;
		
		if (! isset ( $_is_php [$version] )) {
			$_is_php [$version] = (version_compare ( PHP_VERSION, $version ) < 0) ? FALSE : TRUE;
		}
		
		return $_is_php [$version];
	}
}

// ------------------------------------------------------------------------

/**
 * Class registry
 *
 * This function acts as a singleton. If the requested class does not
 * exist it is instantiated and set to a static variable. If it has
 * previously been instantiated the variable is returned.
 *
 * @access public
 * @param
 *        	string the class name being requested
 * @param
 *        	string the directory where the class should be found
 * @param
 *        	string the class name prefix
 * @return object
 */
if (! function_exists ( 'load_class' )) {
	function &load_class($class, $directory = 'module', $prefix = '') {
		static $_classes = array ();
		
		// Does the class exist? If so, we're done...
		if (isset ( $_classes [$class] )) {
			return $_classes [$class];
		}
		
		$name = FALSE;
		
		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries folder
		// foreach (array(APPPATH, BASEPATH) as $path)
		// {
		// if (file_exists($path.$directory.'/'.$class.'.php'))
		// {
		// $name = $prefix.$class;
		//
		// if (class_exists($name) === FALSE)
		// {
		// require($path.$directory.'/'.$class.'.php');
		// }
		//
		// break;
		// }
		// }
		$path = APP . '/';
		if (file_exists ( $path . $directory . '/' . $class . '.php' )) {
			$name = $prefix . $class;
			
			if (class_exists ( $name ) === FALSE) {
				require ($path . $directory . '/' . $class . '.php');
			}
		}
		
		// Did we find the class?
		if ($name === FALSE) {
			// Note: We use exit() rather then show_error() in order to avoid a
			// self-referencing loop with the Excptions class
			exit ( 'Unable to locate the specified class: ' . $class . '.php' );
		}
		
		// Keep track of what we just loaded
		is_loaded ( $class );
		
		$_classes [$class] = new $name ();
		return $_classes [$class];
	}
}

// --------------------------------------------------------------------

/**
 * Keeps track of which libraries have been loaded.
 * This function is
 * called by the load_class() function above
 *
 * @access public
 * @return array
 */
if (! function_exists ( 'is_loaded' )) {
	function &is_loaded($class = '') {
		static $_is_loaded = array ();
		
		if ($class != '') {
			$_is_loaded [strtolower ( $class )] = $class;
		}
		
		return $_is_loaded;
	}
}

// ------------------------------------------------------------------------

if (! function_exists ( 'getIp' )) {
	function getIp() {
		$sapi_type = php_sapi_name ();
		if ("cli" == $sapi_type) {
			$ip = gethostbyname ( gethostname () );
			return $ip;
		}
		
		if (isset ( $_SERVER ['HTTP_CLIENT_IP'] ) && $_SERVER ['HTTP_CLIENT_IP'] != 'unknown') {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
			return $ip;
		}
		
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) && $_SERVER ['HTTP_X_FORWARDED_FOR'] != 'unknown') {
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
			return $ip;
		}
		
		$ip = $_SERVER ['REMOTE_ADDR'];
		return $ip;
	}
}

/**
 * 获取客户端浏览器
 */
if (! function_exists ( 'getbrowse' )) {
	function getbrowse() {
		$user_OSagent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_OSagent, "Maxthon" ) && strpos ( $user_OSagent, "MSIE" )) {
			$visitor_browser = "Maxthon(Microsoft IE)";
		} elseif (strpos ( $user_OSagent, "Maxthon 2.0" )) {
			$visitor_browser = "Maxthon 2.0";
		} elseif (strpos ( $user_OSagent, "Maxthon" )) {
			$visitor_browser = "Maxthon";
		} elseif (strpos ( $user_OSagent, "MSIE 9.0" )) {
			$visitor_browser = "MSIE 9.0";
		} elseif (strpos ( $user_OSagent, "MSIE 8.0" )) {
			$visitor_browser = "MSIE 8.0";
		} elseif (strpos ( $user_OSagent, "MSIE 7.0" )) {
			$visitor_browser = "MSIE 7.0";
		} elseif (strpos ( $user_OSagent, "MSIE 6.0" )) {
			$visitor_browser = "MSIE 6.0";
		} elseif (strpos ( $user_OSagent, "MSIE 5.5" )) {
			$visitor_browser = "MSIE 5.5";
		} elseif (strpos ( $user_OSagent, "MSIE 5.0" )) {
			$visitor_browser = "MSIE 5.0";
		} elseif (strpos ( $user_OSagent, "MSIE 4.01" )) {
			$visitor_browser = "MSIE 4.01";
		} elseif (strpos ( $user_OSagent, "MSIE" )) {
			$visitor_browser = "MSIE 较高版本";
		} elseif (strpos ( $user_OSagent, "NetCaptor" )) {
			$visitor_browser = "NetCaptor";
		} elseif (strpos ( $user_OSagent, "Netscape" )) {
			$visitor_browser = "Netscape";
		} elseif (strpos ( $user_OSagent, "Chrome" )) {
			$visitor_browser = "Chrome";
		} elseif (strpos ( $user_OSagent, "Lynx" )) {
			$visitor_browser = "Lynx";
		} elseif (strpos ( $user_OSagent, "Opera" )) {
			$visitor_browser = "Opera";
		} elseif (strpos ( $user_OSagent, "Konqueror" )) {
			$visitor_browser = "Konqueror";
		} elseif (strpos ( $user_OSagent, "Mozilla/5.0" )) {
			$visitor_browser = "Mozilla";
		} elseif (strpos ( $user_OSagent, "Firefox" )) {
			$visitor_browser = "Firefox";
		} elseif (strpos ( $user_OSagent, "U" )) {
			$visitor_browser = "Firefox";
		} else {
			$visitor_browser = "其它";
		}
		return $visitor_browser;
	}
}
function pre($data) {
	if ("cli" == php_sapi_name ()) {
		echo "\n";
		print_r ( $data );
		echo "\n";
		return true;
	}
	echo "<pre>";
	var_dump ( $data );
	echo "</pre>";
	return true;
}





/**
 * 获取客户端操作系统
 */
if (! function_exists ( 'getOS' )) {
	function getOS() {
		$user_OSagent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_OSagent, "NT 6.1" )) {
			$visitor_os = "Windows 7";
		} elseif (strpos ( $user_OSagent, "NT 5.1" )) {
			$visitor_os = "Windows XP (SP2)";
		} elseif (strpos ( $user_OSagent, "NT 5.2" ) && strpos ( $user_OSagent, "WOW64" )) {
			$visitor_os = "Windows XP 64-bit Edition";
		} elseif (strpos ( $user_OSagent, "NT 5.2" )) {
			$visitor_os = "Windows 2003";
		} elseif (strpos ( $user_OSagent, "NT 6.0" )) {
			$visitor_os = "Windows Vista";
		} elseif (strpos ( $user_OSagent, "NT 5.0" )) {
			$visitor_os = "Windows 2000";
		} elseif (strpos ( $user_OSagent, "4.9" )) {
			$visitor_os = "Windows ME";
		} elseif (strpos ( $user_OSagent, "NT 4" )) {
			$visitor_os = "Windows NT 4.0";
		} elseif (strpos ( $user_OSagent, "98" )) {
			$visitor_os = "Windows 98";
		} elseif (strpos ( $user_OSagent, "95" )) {
			$visitor_os = "Windows 95";
		} elseif (strpos ( $user_OSagent, "NT" )) {
			$visitor_os = "Windows 较高版本";
		} elseif (strpos ( $user_OSagent, "Mac" )) {
			$visitor_os = "Mac";
		} elseif (strpos ( $user_OSagent, "Linux" )) {
			$visitor_os = "Linux";
		} elseif (strpos ( $user_OSagent, "Unix" )) {
			$visitor_os = "Unix";
		} elseif (strpos ( $user_OSagent, "FreeBSD" )) {
			$visitor_os = "FreeBSD";
		} elseif (strpos ( $user_OSagent, "SunOS" )) {
			$visitor_os = "SunOS";
		} elseif (strpos ( $user_OSagent, "BeOS" )) {
			$visitor_os = "BeOS";
		} elseif (strpos ( $user_OSagent, "OS/2" )) {
			$visitor_os = "OS/2";
		} elseif (strpos ( $user_OSagent, "PC" )) {
			$visitor_os = "Macintosh";
		} elseif (strpos ( $user_OSagent, "AIX" )) {
			$visitor_os = "AIX";
		} elseif (strpos ( $user_OSagent, "IBM OS/2" )) {
			$visitor_os = "IBM OS/2";
		} elseif (strpos ( $user_OSagent, "BSD" )) {
			$visitor_os = "BSD";
		} elseif (strpos ( $user_OSagent, "NetBSD" )) {
			$visitor_os = "NetBSD";
		} else {
			$visitor_os = "其它操作系统";
		}
		return $visitor_os;
	}
}



?>
