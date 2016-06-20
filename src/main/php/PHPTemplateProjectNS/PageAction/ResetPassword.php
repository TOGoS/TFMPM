<?php

class PHPTemplateProjectNS_PageAction_ResetPassword extends PHPTemplateProjectNS_PageAction
{
	protected $token;
	protected $password;
	protected $password2;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->token = $params['token'];
		$this->password = $params['password'];
		$this->password2 = $params['password2'];
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$notes = [];
		$swell = false;
		$this->storageHelper->beginTransaction();
		try {
			$tokenAction = $this->tokenModel->getTokenActionIfValid( $this->token, $notes, true, 'reset-password' );
			
			if( $tokenAction === null ) {
				return $this->templateResponse( 200, 'reset-password', [
					'token' => $this->token,
					'errorMessages' => $notes,
				], null, $actx );
			}

			if( $this->password != $this->password2 ) {
				return $this->templateResponse( 200, 'reset-password', [
					'token' => $this->token,
					'errorMessages' => ["Passwords don't match!"],
				], null, $actx );
			}
			
			$this->storageHelper->doQuery(
				"UPDATE phptemplateprojectdatabasenamespace.user\n".
				"SET passhash = {passhash}\n".
				"WHERE id = {userId}",
				[
					'userId' => $tokenAction['half user ID'],
					'passhash' => $this->userModel->hashPassword( $this->password )
				]
			);
			
			$swell = true;
			
			return $this->redirect( 303, $actx->absoluteUrl('/login') );
		} finally {
			$this->storageHelper->endTransaction($swell);
		}
	}
}
