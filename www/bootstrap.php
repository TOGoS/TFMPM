<?php

// If everything goes hunky-dory then this will be overridden later.
// This helps make fatal errors more obvious.
header('HTTP/1.0 500 Error By Default');

// Turn off any output buffering
while( ob_get_level() ) ob_end_clean();

ini_set('display_errors','on');
ini_set('error_reporting', E_ALL|E_STRICT);

require '../init-www-error-handling.php';

$registry = require '../init-environment.php';
$dispatcher = $registry->getComponent('PHPTemplateProjectNS_Dispatcher');

if( isset($_SERVER['PATH_INFO']) ) {
	// bootstrap.php/yaddah-yaddah
	$path = $_SERVER['PATH_INFO'];
	$bubble404s = false;
} else {
	// php -S ... bootstrap.php
	$path = $_SERVER['REQUEST_URI'];
	$bubble404s = true;
}

$response = $dispatcher->handleImplicitRequest( $path );

// If we're being called by PHP's built-in web server and we
// don't know about some resource, return false to indicate
// to the server that it should go look for other files in www/
if( $bubble404s and $response->getStatusCode() == 404 ) return false;

Nife_Util::outputResponse( $response );
