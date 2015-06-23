<?php

class PHPTemplateProjectNS_Request
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
		$ctx = new PHPTemplateProjectNS_Request($superGlobals);
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
	protected $pathInfo = null;
	protected $userId = null;
	protected $requestContentFuture = null;
	
	protected function set( array $updates ) {
		foreach( $updates as $k => $v ) {
			if( !property_exists($this, $k) )
				throw new Exception("No such attribute as ".get_class($this).'#'.$k);
			$this->$k = $v;
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
	
	public function withRequestContentFuture(callable $rcf) {
		return $this->with('requestContentFuture', $rcf);
	}
	public function getRequestContent() {
		if( $this->requestContentFuture === null ) {
			throw new Exception("No request content future provided.");
		}
		return call_user_func($this->requestContentFuture);
	}
	public function getRequestContentType() {
		return $this->SERVER['CONTENT_TYPE'];
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
		return $this->GET + $this->POST;
	}
	
	public function getQueryString() {
		return isset($this->SERVER['QUERY_STRING']) ? $this->SERVER['QUERY_STRING'] : null;
	}
	
	public function getParam($name,$default=null) {
		foreach( ['POST','GET'] as $l ) {
			if( isset($this->superGlobals[$l][$name]) ) return $this->superGlobals[$l][$name];
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
	
	// Sometimes you want to show an error message on a page.  But you
	// don't want that message to show because of something unrelated
	// to the page, so you don't want to rely solely on a session
	// variable, and you don't want it to show up just because the user
	// uses the same URL to get to that page again (because maybe the
	// error no longer applies), so you can't rely solely on URL parameters.
	// 
	// Solution: use a URL parameter (?error-id=...)  in combination
	// with a session variable (errorMessage).  Only show the message
	// when they match.
	
	/**
	 * Set error message in the session and return an error message ID
	 * to stick in an error-id parameter.
	 */
	public function setErrorMessage($message) {
		$errorId = TOGoS_Base32::encode(hash('sha1',$message,true));
		$this->setSessionVariable('errorMessage', $message);
		return $errorId;
	}
	
	/**
	 * If the last message set by setErrorMessage matches the ID given,
	 * (which defaults to that in the 'error-id' URL parameter) return
	 * that message.  Otherwise return null, indicating no current
	 * error message.
	 */
	public function getErrorMessage($messageId='current') {
		if( $messageId == 'current' ) $messageId = $this->getParam('error-id');
		$message = $this->getSessionVariable('errorMessage');
		if( $message === null ) return null;
		$hash = TOGoS_Base32::encode(hash('sha1',$message,true));
		return $hash === $messageId ? $message : null;
	}
}
