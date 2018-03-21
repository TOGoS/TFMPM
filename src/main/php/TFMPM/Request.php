<?php

class TFMPM_Request
{
	public static function fromEnvironment($otherSettings=[]) {
		$superGlobals = [];
		foreach( $GLOBALS as $k=>$v ) {
			if( $k[0] == '_' and $k != '_SESSION' ) {
				// Session not registered because it is handled separately.
				// (See session methods, way down below.)
				$superGlobals[substr($k,1)] = $v;
			}
		}
		$ctx = new TFMPM_Request($superGlobals);
		return $ctx->with($otherSettings);
	}
	
	protected $superGlobals;
	
	public function __construct(array $superGlobals, array $otherStuff=[]) {
		$this->superGlobals = $superGlobals;
		$this->set($otherStuff);
	}
	
	protected function getBasicAuth() {
		foreach( ['HTTP_AUTHORIZATION','REDIRECT_HTTP_AUTHORIZATION'] as $v ) {
			if( !empty($this->SERVER[$v]) and preg_match('/^Basic (.*)$/',$this->SERVER[$v],$bif) ) {
				return $bif[1];
			}
		}
		return null;
	}
	
	public function getAuthUserPw() {
		if( isset($this->SERVER['PHP_AUTH_USER']) ) {
			return ['username'=>$this->SERVER['PHP_AUTH_USER'], 'password'=>$this->SERVER['PHP_AUTH_PW']];
		} else if( ($ba = $this->getBasicAuth()) !== null ) {
			list($un,$pw) = explode(':',base64_decode($ba),2);
			return ['username'=>$un,'password'=>$pw];
		}
		return ['username'=>null,'password'=>null];
	}
	
	public function basicAuthProvided() {
		$b = $this->getAuthUserPw();
		return $b['username'] !== null;
	}
	
	/**
	 * Returns a session ID usable by the iPad app.
	 * Completely not compatible with PHPSESSID.
	 */
	public function getClientAppSessionId() {
		if( isset($this->SERVER['PHP_AUTH_USER']) ) {
			return base64_encode($this->SERVER['PHP_AUTH_USER'].':'.$this->SERVER['PHP_AUTH_PW']);
		} else {
			return null;
		}
	}
	
	protected $requestMethod = null;
	/** PATH_INFO, but not pre-URL-decoded; e.g. http://mysite/xyz%20123 -> pathInfo = '/xyz%20123' */
	protected $pathInfo = null;
	protected $queryString = null;
	protected $userId = null;
	protected $requestContentFuture = null;
	protected $requestContent = null;
	
	protected function set( array $updates ) {
		foreach( $updates as $k => $v ) {
			$setterMethod = "set".ucfirst($k);
			if( method_exists($this,$setterMethod) ) {
				$this->$setterMethod($v);
			} else if( property_exists($this, $k) ) {
				$this->$k = $v;
			} else {
				throw new Exception("No such attribute as ".get_class($this).'#'.$k);
			}
		}
	}
	
	public function with( $k, $v=null ) {
		if( !is_array($k) ) {
			return $this->with( [$k=>$v] );
		}
		
		$updates = [];
		foreach( $k as $k1=>$v1 ) {
			if( $this->$k1 === $v1 ) continue;
			$updates[$k1] = $v1;
		}
		
		if( count($updates) == 0 ) return $this;
		
		$newMe = clone $this;
		$newMe->set( $updates );
		return $newMe;
	}
	
	public function withPathInfo($pathInfo) { return $this->with('pathInfo', $pathInfo); }
	public function getPathInfo() { return $this->pathInfo; }
	
	public function withUserId($userId) { return $this->with('userId', $userId); }
	public function getUserId() { return $this->userId; }
	
	// Request content is complicated because it can be represented a lot of different ways:
	// - as a future or not
	// - as a blob, a string, or an object to be encoded
	// For consistency's sake we should probably change to simply storing
	// - blob future
	// since that can easily handle all other cases.
	
	public function withRequestContentFuture(callable $rcf) {
		return $this->with('requestContentFuture', $rcf);
	}
	public function getRequestContent() {
		if( $this->requestContent ) {
			return $this->requestContent;
		}
		if( $this->requestContentObject !== null ) {
			// I guess JSON-encode it?  Usually things go the other way.
			return EarthIT_JSON::prettyEncode($this->requestContentObject);
		}
		if( $this->requestContentFuture !== null ) {
			return call_user_func($this->requestContentFuture);
		}
		throw new Exception("No requestContentObject or requestContentFuture provided.");
	}
	public function getRequestContentType() {
		return isset($this->SERVER['CONTENT_TYPE']) ? $this->SERVER['CONTENT_TYPE'] : null;
	}
	public function getRequestContentBlob() {
		return new Nife_StringBlob($this->getRequestContent());
	}
	
	/**
	 * Return the object encoded by the request IFF
	 * It is a recognized form, consuming php://input if necessary.
	 * Otherwise returns null.
	 */
	protected function _getRequestContentObject() {
		switch( $this->SERVER['REQUEST_METHOD'] ) {
		case 'GET': case 'HEAD':
			$requestContentObject = null;
			break;
		default:
			$contentType = $this->requestContentType;
			preg_match( '/^([^\s;]+)/', $contentType, $bif );
			$baseContentType = $bif[1];
			// Assuming charset=utf8, so not bothering to parse that out.
				
			switch( $baseContentType ) {
			case 'application/x-www-form-urlencoded':
				$requestContentObject = $this->superGlobals['POST'];
				break;
			case 'application/json':
				$requestContent = $this->getRequestContent();
				$requestContentObject = $requestContent == '' ? null : EarthIT_JSON::decode($requestContent);
				break;
			default:
				return null;
				//throw new Exception("Don't know how to parse request content; type = '{$contentType}'");
			}
		}
		return $requestContentObject;
	}
	
	protected $requestContentObject;
	public function withRequestContentObject($v) { return $this->with('requestContentObject', $v); }
	public function getRequestContentObject() {
		if( $this->requestContentObject === null ) {
			$this->requestContentObject = $this->_getRequestContentObject();
		}
		return $this->requestContentObject;
	}
	
	/**
	 * As with other set* methods, don't use this directly; use with(...) to construct a new Request object
	 */
	public function setRequestContent( $content ) {
		$this->requestContent = (string)$content;
	}
	
	
	public function getRequestHeaderValue( $k, $default=null ) {
		$k = str_replace('-','_',strtoupper($k));
		return isset($this->SERVER["HTTP_{$k}"]) ? $this->SERVER["HTTP_{$k}"] : $default;
	}
	
	public function getRequestMethod() {
		if( isset($this->requestMethod) ) {
			return $this->requestMethod;
		} else if( isset($this->SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ) {
			return $this->SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		} else {
			return $this->SERVER['REQUEST_METHOD'];
		}
	}
	
	public function getParams() {
		$rco = $this->getRequestContentObject() or $rco = array();
		return $this->GET + $this->POST + $rco;
	}
	
	public function getQueryString() {
		return $this->queryString !== null ? $this->queryString :
			(isset($this->SERVER['QUERY_STRING']) ? $this->SERVER['QUERY_STRING'] : null);
	}
	
	public function getParam($name,$default=null) {
		foreach( ['POST','GET'] as $l ) {
			if( isset($this->superGlobals[$l][$name]) ) return $this->superGlobals[$l][$name];
		}
		if( ($rco = $this->getRequestContentObject()) and isset($rco[$name]) ) {
			return $rco[$name];
		}
		return $default;
	}
	
	public function __get($name) {
		$getMeth = 'get'.ucfirst($name);
		if( method_exists($this,$getMeth) ) return $this->$getMeth();
		if( $name == 'REQUEST' ) return $this->GET + $this->POST;
		return $this->superGlobals[$name];
	}
	
	public function __isset($name) {
		return isset($this->superGlobals[$name]);
	}
	
	//// Session stuff!
	// If you need to ~set~ session variables, use your ActionContext.
	
	protected function openSession() {
		if( session_status() == PHP_SESSION_NONE ) session_start();
	}
	
	protected function openSessionIfExists() {
		if( isset($_COOKIE['PHPSESSID']) ) $this->openSession();
	}
	
	public function getSessionVariable($key) {
		$this->openSessionIfExists();
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}
}
