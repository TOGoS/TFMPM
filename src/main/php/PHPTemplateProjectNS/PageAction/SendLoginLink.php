<?php

class PHPTemplateProjectNS_PageAction_SendLoginLink extends PHPTemplateProjectNS_PageAction
{
	protected $emailAddress;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $emailAddress ) {
		parent::__construct($reg);
		$this->emailAddress = $emailAddress;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$errors = [];
		
		$userId = $this->storageHelper->queryValue(
			"SELECT id FROM phptemplateprojectdatabasenamespace.user WHERE emailaddress = {ea}",
			['ea' => $this->emailAddress]
		);
		if( $userId === null ) {
			// We might not want to admit this, but for testing purposes...
			$errors[] = "No user record for e-mail address '{$this->emailAddress}' exists.";
		} else {
			$this->tokenModel->
		}
		
		
		$loginResult = $this->userModel->checkLogin( $this->username, $this->password );
		if( $loginResult['success'] ) {
			$actx->setSessionVariable('userId', $loginResult['userId']);
			return $this->redirect(303, $actx->relativeUrl('/'));
		} else {
			return $this->redirectWithErrorMessage($actx->relativeUrl('/login'), $loginResult['message'], $actx);
		}
	}
}
