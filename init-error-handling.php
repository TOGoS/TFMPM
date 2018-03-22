<?php

if( !defined('DEBUG_BACKTRACE_IGNORE_ARGS') ) define('DEBUG_BACKTRACE_IGNORE_ARGS', false);

// Last resort error handling.
// This should be as robust as possible.
// i.e. try very hard to give you some output when everything goes haywire,
// even if that occurs very early on, before libraries have been loaded, etc.
// It should also be able to handle very long traces without running out of memory,
// because blank screens are not helpful.

if( !class_exists('Nife_Blob', false) ) {
	interface Nife_Blob {
		public function getLength();
		public function writeTo($callback);
	}
}

/** A string collector */
class TFMPM_Thneed {
	protected $items;
	public function __invoke($item) {
		$this->items[] = $item;
	}
	public function __toString() {
		return implode('', $this->items);
	}
}

class TFMPM_StreamWriter {
	protected $stream;
	public function __construct($stream) {
		$this->stream = $stream;
	}
	public function __invoke($item) {
		fwrite( $this->stream, $item );
	}
}

class TFMPM_ErrorTraceBlob implements Nife_Blob {
	protected $text;
	protected $backtrace;
	protected $cause;
	public function __construct( $text, array $backtrace=null, $cause=null ) {
		$this->text = $text;
		$this->backtrace = $backtrace;
		$this->cause = $cause;
	}
	
	public function getLength() { return null; }
	
	public function writeTo( $callback ) {
		$text = $this->text;
		$backtrace = $this->backtrace;
		$cause = $this->cause;
		
		while( true ) {
			call_user_func($callback, "{$text}\n");
			foreach( $backtrace as $item ) {
				if( isset($item['file']) || isset($item['line']) ) {
					$f = isset($item['file']) ? $item['file'] : '';
					$l = isset($item['line']) ? $item['line'] : '';
					$u = isset($item['function']) ? $item['function'] : '';
					call_user_func($callback, "  " . $f . ($l ? ":{$l}" : '') . ($u ? " in {$u}" : '') . "\n");
				}
			}
			if( $cause == null ) return;
			
			call_user_func($callback, "Caused by...\n");
			$text = get_class($cause).": ".$cause->getMessage();
			$backtrace = array_merge( array(array('file'=>$cause->getFile(), 'line'=>$cause->getLine())), $cause->getTrace());
			$cause = $cause->getPrevious();
		}
	}
	
	public function __toString() {
		$thneed = new TFMPM_Thneed();
		$this->writeTo($thneed);
		return (string)$thneed;
	}
	
	public static function forPhpError( $errno, $errstr, $errfile=null, $errline=null, $errcontext=null, $backtrace=array() ) {
		return new self("Error code=$errno: $errstr", $backtrace);
	}
	
	public static function forException( $ex ) {
		return new self(
			get_class($ex).": ".$ex->getMessage(),
			array_merge( array(array('file'=>$ex->getFile(), 'line'=>$ex->getLine())), $ex->getTrace()),
			$ex->getPrevious()
		);
	}
}

class TFMPM_Error_Handler {
	public $dumpStream;
	public $bypassOnShutdown = false;
	/** A map of log filename => error message */
	public $loggingFailures = array();
	
	public function __construct( $dumpStream ) {
		$this->dumpStream = $dumpStream;
	}
	
	public static function create() {
		$dumpStream = fopen(PHP_SAPI === 'cli' ? 'php://stderr' : 'php://output','wb');
		return new self($dumpStream);
	}
	
	/** Write a blob or string $thing to a stream */
	protected static function write($thing, $stream) {
		if( is_scalar($thing) ) {
			fwrite($stream, $thing);
		} else if( $thing instanceof Nife_Blob ) {
			$thing->writeTo( new TFMPM_StreamWriter($stream) );
		} else {
			fwrite($stream, "<weird error text value: ".gettype($thing).">");
		}
	}
	
	public function logErrorText($logText) {
		$logsDir = __DIR__.'/logs';
		if( !is_dir($logsDir) ) {
			if( !@mkdir($logsDir) ) {
				$this->loggingFailures[$logsDir] = "Failed to mkdir";
				return false;
			}
		}
		
		$logFile = $logsDir.'/error.log';
		$logStream = @fopen($logFile, 'a');
		if( $logStream === false ) {
			$this->loggingFailures[$logFile] = "Failed to fopen('$logFile','a')";
			return false;
		}
		fwrite($logStream, date('## c')."\n");
		self::write($logText, $logStream);
		fwrite($logStream, "\n");
		fclose($logStream);
		return true;
	}
	
	public function sendErrorHeaders( $status ) {
		if( !headers_sent() ) {
			header("HTTP/1.0 $status");
			header("Status: $status");
			header('Content-Type: text/plain');
			ini_set('html_errors', false);
		}
	}
	
	public function logAndDump( $text ) {
		self::write($text, $this->dumpStream);
		
		$this->logErrorText($text);

		if( $this->loggingFailures ) {
			fwrite($this->dumpStream, "\n");
			fwrite($this->dumpStream, "Also failed to write log files:\n");
			foreach( $this->loggingFailures as $logFile=>$message ) {
				fwrite($this->dumpStream, "  $logFile: {$message}\n");
			}
		}
	}
	
	public function onException( $ex ) {
		$this->sendErrorHeaders( "500 Uncaught Exception" );
		$this->logAndDump( TFMPM_ErrorTraceBlob::forException($ex) );
		$this->bypassOnShutdown = true;
		exit(1);
	}
	
	public function onPhpError($errno, $errstr, $errfile=null, $errline=null, $errcontext=null) {
		if( (error_reporting() & $errno) == 0 ) return; // @fopen, etc.
		$this->sendErrorHeaders( "500 PHP Error" );
		$this->logAndDump( TFMPM_ErrorTraceBlob::forPhpError(
			$errno, $errstr, $errfile, $errline, $errcontext, debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS)
		));
		$this->bypassOnShutdown = true;
		exit(1);
	}

	public function onShutdown() {
		if( $this->bypassOnShutdown ) return;
		
		// To log fatal errors
		$error = error_get_last();
		if( $error !== null ) {
			$this->sendErrorHeaders('500 PHP Fatal Error');
			$this->logAndDump(
				"Fatal error occurred.\n".
				"({$error['type']}) {$error['message']} at {$error['file']}:{$error['line']}\n"
			);
			exit(1);
		}
	}
	
	public function register() {
		set_error_handler(array($this,'onPhpError'), E_ALL|E_STRICT);
		set_exception_handler(array($this,'onException'));
		register_shutdown_function(array($this,'onShutdown'));
	}
}

global $TFMPM_ErrorHandler;
$TFMPM_ErrorHandler = TFMPM_Error_Handler::create();
$TFMPM_ErrorHandler->register();
