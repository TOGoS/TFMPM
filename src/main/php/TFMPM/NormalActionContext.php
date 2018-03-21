<?php

class TFMPM_NormalActionContext extends TFMPM_Component implements TFMPM_ActionContext
{
	protected $pathInfo;
	protected $loggedInUserId;
	
	public function getLoggedInUserId() {
		return $this->loggedInUserId;
	}
	
	protected function openSession() {
		if( session_status() == PHP_SESSION_NONE ) session_start();
	}
	
	public function sessionExists() {
		return isset($_COOKIE['PHPSESSID']);
	}
	
	protected function openSessionIfExists() {
		if( $this->sessionExists() ) $this->openSession();
	}
	
	public function getSessionVariable($key, $default=null) {
		$this->openSessionIfExists();
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}
	
	public function setSessionVariable($key, $value) {
		if( $value === null ) {
			$this->unsetSessionVariable($key);
			return;
		}
		
		$this->openSession();
		$_SESSION[$key] = $value;
	}
	
	public function unsetSessionVariable($key) {
		$this->openSessionIfExists();
		unset($_SESSION[$key]);
	}
	
	public function destroySession() {
		if( !$this->sessionExists() ) return; // Nothing to destroy!
		
		$this->openSession(); // We'll get an error if we try to destroy a non-open session.
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		session_destroy();
	}
	
	/** Don't use this unless you're #with */
	public function set(array $params) {
		foreach( $params as $k=>$v ) {
			if( property_exists(get_class($this), $k) ) {
				$this->$k = $v;
			} else {
				throw new Exception("No such property: $k");
			}
		}
	}
	
	public function with(array $params) {
		$c = clone $this;
		$c->set($params);
		return $c;
	}
	
	/** @override */
	public function getPathInfo() {
		return $this->pathInfo;
	}

	/** @override */
	public function relativeUrl($path) {
		if( $path[0] == '/' ) $path = substr($path,1);
		$p = str_repeat('../', substr_count($this->pathInfo,'/')-1);
		$p .= $path;
		return $p == '' ? './' : $p;
	}
	
	/** @override */
	public function absoluteUrl($path) {
		if( $path[0] == '/' ) $path = substr($path,1);
		return $this->registry->getConfig('www/deployment-url-prefix').$path;
	}
}
