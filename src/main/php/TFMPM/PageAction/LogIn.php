<?php

class TFMPM_PageAction_LogIn extends TFMPM_PageAction
{
	protected $username;
	protected $password;
	
	public function __construct( TFMPM_Registry $reg, $username, $password ) {
		parent::__construct($reg);
		$this->username = $username;
		$this->password = $password;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$loginResult = $this->userModel->checkLogin( $this->username, $this->password );
		if( $loginResult['success'] ) {
			$actx->setSessionVariable('userId', $loginResult['userId']);
			return $this->redirect(303, $actx->relativeUrl('/'));
		} else {
			return $this->redirectWithErrorMessage($actx->relativeUrl('/login'), $loginResult['message'], $actx);
		}
	}
}
