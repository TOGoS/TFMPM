<?php

class TFMPM_PageAction_Register extends TFMPM_PageAction
{
	protected $name;
	protected $emailAddress;
	protected $loginLinkRequested;
	
	public function __construct( TFMPM_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->name = $params['name'];
		$this->emailAddress = $params['e-mail-address'];
		$this->loginLinkRequested = !empty($params['send-login-link']);
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$newMessage = new Swift_Message();
		try {
			$newMessage->setTo( $this->emailAddress );
		} catch( Swift_RfcComplianceException $e ) {
			return $this->redirectWithErrorMessage(
				'register',
				["The supplied e-mail address, '{$this->emailAddress}', is invalid",$e->getMessage()],
				$actx);
		}
		
		$okay = false;
		// If anything goes wrong we'll roll it all back
		// so that they can try again later with the same username
		$this->storageHelper->beginTransaction();
		try {
			$existingUser = $this->storageHelper->getItem('user', ['e-mail address'=>$this->emailAddress]);
			if( $existingUser !== null ) {
				return $this->redirectWithErrorMessage(
					'register',
					"The supplied e-mail address, '{$this->emailAddress}', is already taken.",
					$actx);
			}
			
			$newUserId = $this->storageHelper->newEntityId();
			
			$user = [
				'ID' => $newUserId,
				'username' => $this->name,
				'e-mail address' => $this->emailAddress,
			];
			
			$this->storageHelper->insertNewItem('user', $user);			

			$ps = ["Thanks for registering, {$this->name}!"];
			
			$pwrAction = $this->tokenModel->newTokenAction( [
				'half user ID' => $newUserId,
				'action script' => 'reset-password',
				're-useable' => false,
			] );
			$ps[] =
				"Go here to set your password:\n".
				$actx->absoluteUrl("/reset-password?token={$pwrAction['token']}");
			
			if( $this->loginLinkRequested ) {
				$loginAction = $this->tokenModel->newTokenAction( [
					'half user ID' => $newUserId,
					'action script' => 'log-in',
					're-useable' => true,
				] );
				$ps[] =
					"To log in without a password, use this link:\n".
					$actx->absoluteUrl("/do-token?token={$loginAction['token']}");
			}
			
			$newMessage->setFrom( $this->registry->getSwiftAddress('registration') );
			$newMessage->setSubject( "Thank you for registering!" );
			$newMessage->setBody( implode("\n\n", $ps) );
			
			$this->mailer->send( $newMessage );
			
			$okay = true;
		} finally {
			$this->storageHelper->endTransaction($okay);
		}
		
		return Nife_Util::httpResponse(200, "Confirmation message sent to {$this->emailAddress}");
	}
}
