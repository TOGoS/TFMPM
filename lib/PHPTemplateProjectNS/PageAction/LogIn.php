<?php

class PHPTemplateProjectNS_PageAction_LogIn extends PHPTemplateProjectNS_PageAction
{
	protected $username;
	protected $password;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $username, $password ) {
		parent::__construct($reg);
		$this->username = $username;
		$this->password = $password;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$loginResult = $this->userModel->checkLogin( $this->username, $this->password );
		if( $loginResult['success'] ) {
			$actx->setSessionVariable('userId', $loginResult['userId']);
			return $this->redirect(303, $actx->relativeUrl('/'));
		} else {
			return $this->redirectWithErrorMessage($actx->relativeUrl('/login'), $loginResult['message'], $actx);
		}
	}
}
