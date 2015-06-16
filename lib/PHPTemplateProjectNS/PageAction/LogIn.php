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
		
		// TODO: Check based on username or e-mail, then if there is ambiguity, just e-mail
		
		$possibleUsers = [];
		foreach( $users as $user ) {
			if( $this->userModel->checkPassword($this->password, $user['passhash']) ) {
				$possibleUsers[] = $user;
			}
		}

		switch( count($possibleUsers) ) {
		case 1:
			foreach($possibleUsers as $user) {
				$actx->setLoggedInUserId($user['ID']);
				return $this->redirect(303, './');
			}
		case 0:
			return $this->redirectWithErrorMessage(
				'./login',
				"Invalid login credentials.",
				$actx);
		default:
			return $this->redirectWithErrorMessage(
				'./login',
				"Ambiguous login!",
				$actx);
		}
	}
}
