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
$response = $dispatcher->handleRequest( $_SERVER['PATH_INFO'] );

Nife_Util::outputResponse( $response );
