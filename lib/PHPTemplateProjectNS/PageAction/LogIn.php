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
		$users = $this->storageHelper->queryItems(
			'user',
			"SELECT *\n".
			"FROM phptemplateprojectdatabasenamespace.user\n".
			"WHERE username = {username} OR emailaddress = {username}\n",
			['username'=>$this->username]);
		
		if( count($users) > 1 ) {
			return $this->redirectWithErrorMessage(
				'./login',
				"More than one user matches those credentials.  Try using an e-mail address instead of username.",
				$actx);
		}
		
		foreach( $users as $user ) {
			$actx->setLoggedInUserId($user['ID']);
			return $this->redirect(303, './');
		}
		
		return $this->redirectWithErrorMessage(
			'./login',
			"Invalid login credentials.",
			$actx);
	}
}
