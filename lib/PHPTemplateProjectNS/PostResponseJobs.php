<?php

/**
 * A place to enqueue work to be done after the request has been
 * responded to.
 */
class PHPTemplateProjectNS_PostResponseJobs
{
	protected static $jobs = array();
	
	public static function enqueue($job) {
		self::$jobs[] = $job;
	}
	
	public static function run() {
		if( count(self::$jobs) == 0 ) return;
		
		// Try to close the connection
		if( function_exists('fastcgi_finish_request') ) fastcgi_finish_request();
		// or at least flush anything we've written to it
		// (which helps in cases where content-length was known)
		else flush();
		
		foreach( self::$jobs as $j ) {
			call_user_func($j);
		}
		self::$jobs = array();
	}
}
