<?php

class PHPTemplateProjectNS_NormalActionContext implements PHPTemplateProjectNS_ActionContext
{
	public function getLoggedInUserId() {
		return $this->getSessionVariable('userId');
	}
	public function setLoggedInUserId($userId) {
		$this->setSessionVariable('userId', $userId);
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
}
