<?php

class TFMPM_FakeActionContext implements TFMPM_ActionContext
{
	protected $loggedInUserId;
	protected $session = null;
	protected $pathInfo;
	
	public function __construct($loggedInUserId=null, $pathInfo=null) {
		$this->loggedInUserId = $loggedInUserId;
		$this->pathInfo = $pathInfo;
	}
	
	public function _set( array $props ) {
		foreach( $props as $k=>$v ) $this->$k = $v;
	}
	
	public function with( array $props ) {
		$c = clone $this;
		$c->_set($props);
		return $c;
	}
	
	public function getLoggedInUserId() { return $this->loggedInUserId; }
	public function sessionExists() { return isset($this->session); }
	public function getSessionVariable($key, $default=null) {
		return isset($this->session[$key]) ? $this->session[$key] : $default;
	}
	public function setSessionVariable($key, $value) {
		$this->session[$key] = $value;
	}
	public function unsetSessionVariable($key) {
		unset($this->session[$key]);
	}
	public function destroySession() {
		$this->session = null;
	}
	public function getPathInfo() {
		return $this->pathInfo;
	}
	public function relativeUrl($path) {
		return $path;
	}
	public function absoluteUrl($path) {
		return 'http://fake.example.com'.$path;
	}
}
