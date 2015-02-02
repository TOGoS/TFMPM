<?php

// If everything goes hunky-dory then this will be overridden later.
// This helps make fatal errors more obvious.
header('HTTP/1.0 500 Error By Default');

// Turn off any output buffering
while( ob_get_level() ) ob_end_clean();

ini_set('display_errors','on');
ini_set('error_reporting', E_ALL|E_STRICT);

require_once __DIR__.'/../init-www-error-handling.php';
require_once __DIR__.'/../init-environment.php';

$dispatcher = $PHPTemplateProjectNS_Registry->dispatcher;

if( isset($_SERVER['PATH_INFO']) ) {
	$path = $_SERVER['PATH_INFO'];
} else {
	preg_match('/^([^?]*)/',$_SERVER['REQUEST_URI'],$bif);
	$path = $bif[1];
}

$response = $dispatcher->handleImplicitRequest( $path );

$bubble404s = preg_match('/^PHP.*Development Server$/', $_SERVER['SERVER_SOFTWARE']);

// If we're being called by PHP's built-in web server and we
// don't know about some resource, return false to indicate
// to the server that it should go look for other files in www/
if( $bubble404s and $response->getStatusCode() == 404 ) return false;

Nife_Util::outputResponse( $response );
