<?php

namespace tool;

if ("cli" != php_sapi_name ()) {
	// header ( "HTTP/1.0 404 Not Found" );
	exit ();
}
class Gear extends \FrontBase {
	
}
?>
