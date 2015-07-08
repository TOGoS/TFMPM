<?php

if( !defined('DEBUG_BACKTRACE_IGNORE_ARGS') ) define('DEBUG_BACKTRACE_IGNORE_ARGS', false);

global $eit_error_stream;
$eit_error_stream = fopen(PHP_SAPI === 'cli' ? 'php://stderr' : 'php://output','wb');

function eit_send_error_headers( $status ) {
	if( !headers_sent() and PHP_SAPI !== 'cli' ) {
		header("HTTP/1.0 $status");
		header("Status: $status");
		header('Content-Type: text/plain');
	}
}

function eit_dump_error_and_exit2( $text, $backtrace, Exception $cause=null ) {
	global $eit_error_stream;
	eit_send_error_headers( "500 Server Error" );
	fwrite($eit_error_stream, "{$text}\n");
	foreach( $backtrace as $item ) {
		if( isset($item['file']) || isset($item['line']) ) {
			$f = isset($item['file']) ? $item['file'] : '';
			$l = isset($item['line']) ? $item['line'] : '';
			$u = isset($item['function']) ? $item['function'] : '';
			fwrite($eit_error_stream, "  " . $f . ($l ? ":{$l}" : '') . ($u ? " in {$u}" : '') . "\n");
		}
	}
	if( $cause != null ) {
		fwrite($eit_error_stream, "Caused by...\n");
		eit_dump_exception_and_exit($cause);
	}
	exit(1);
}

function eit_dump_error_and_exit( $errno, $errstr, $errfile=null, $errline=null, $errcontext=null ) {
	eit_dump_error_and_exit2( "Error code=$errno: $errstr", debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
}

function eit_dump_exception_and_exit( Exception $ex ) {
	eit_dump_error_and_exit2(
		$ex->getMessage(),
		array_merge( array(array('file'=>$ex->getFile(), 'line'=>$ex->getLine())), $ex->getTrace()),
		$ex->getPrevious()
	);
}

function eit_on_shutdown() {
	global $eit_error_stream;
	// To log fatal errors
	$error = error_get_last();
	if( $error !== null ) {
		eit_send_error_headers('500 PHP Fatal Error');
		fwrite($eit_error_stream, "Fatal error occurred.\n");
		fwrite($eit_error_stream, "({$error['type']}) {$error['message']} at {$error['file']}:{$error['line']}\n");
		eit_dump_error_and_exit( $error['type'], $error['message'], $error['file'], $error['line'] );
	}
}

function register_eit_error_handlers() {
	// I don't recommend the ErrorException approach mentioned
	// on http://us2.php.net/manual/en/class.errorexception.php because
	// 1) Errors (almost) always indicate a problem with your code that should be fixed, and
	// 2) It can cause problems in contexts where there is no stack frame.
	set_error_handler('eit_dump_error_and_exit', E_ALL|E_STRICT);
	set_exception_handler('eit_dump_exception_and_exit');
	register_shutdown_function('eit_on_shutdown');
}

register_eit_error_handlers();
