<?php

class TFMPM_PageAction_ResetPassword extends TFMPM_PageAction
{
	protected $token;
	protected $password;
	protected $password2;
	
	public function __construct( TFMPM_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->token = $params['token'];
		$this->password = $params['password'];
		$this->password2 = $params['password2'];
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
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
				"UPDATE tfmpm.user\n".
				"SET passhash = {passhash}\n".
				"WHERE id = {userId}",
				[
					'userId' => $tokenAction['half user ID'],
					'passhash' => $this->passwordModel->hashPassword( $this->password )
				]
			);
			
			$swell = true;
			
			return $this->redirect( 303, $actx->absoluteUrl('/login') );
		} finally {
			$this->storageHelper->endTransaction($swell);
		}
	}
}
