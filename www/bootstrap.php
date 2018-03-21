<?php

// If everything goes hunky-dory then this will be overridden later.
// This helps make fatal errors more obvious.
header('HTTP/1.0 500 Error By Default');

// Turn off any output buffering
while( ob_get_level() ) ob_end_clean();

ini_set('display_errors','on');
ini_set('error_reporting', E_ALL|E_STRICT);

require_once __DIR__.'/../init-error-handling.php';
require_once __DIR__.'/../init-environment.php';

$router = $TFMPM_Registry->router;

// Depending how this was invoked (mod_php, cgi, PHP's built-in
// server, etc) PATH_INFO may be set in various ways.  This code
// attempts to catch some of them.
// 
// For environments that are more wildly different, you might want
// to just have separate bootstrap scripts.

if( isset($_SERVER['PATH_INFO']) ) {
	// Unfortunately PATH_INFO is URL-decoded before we get at it.
	// Which means there's no way to e.g. encode a '/' as part of a single path component.
	// :(
	$path = str_replace('%','%25',$_SERVER['PATH_INFO']);
} else {
	preg_match('/^([^?]*)(?:\?(.*))?$/',$_SERVER['REQUEST_URI'],$bif);
	$path = $bif[1];
	if(!isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['QUERY_STRING'] = isset($bif[2]) ? $bif[2] : '';
	}
}

$request = TFMPM_Request::fromEnvironment([
	'pathInfo'=>$path,
	'requestContentFuture'=>'eit_get_request_content'
]);
$actx = new TFMPM_NormalActionContext($TFMPM_Registry);
$actx = $actx->with(array('pathInfo'=>$path));
$response = $router->handleRequest($request, $actx);

$bubble404s = preg_match('/^PHP.*Development Server$/', $_SERVER['SERVER_SOFTWARE']);

// If we're being called by PHP's built-in web server and we
// don't know about some resource, return false to indicate
// to the server that it should go look for other files in www/
if( $bubble404s and $response->getStatusCode() == 404 ) return false;

Nife_Util::outputResponse( $response );

TFMPM_PostResponseJobs::run();
