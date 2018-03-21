<?php

class TFMPM_PageAction_SendPasswordResetLink extends TFMPM_PageAction
{
	protected $emailAddress;
	
	public function __construct( TFMPM_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->emailAddress = $params['email-address'];
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$user = $this->storageHelper->getItem('user', ['e-mail address'=>$this->emailAddress]);
		if( $user === null ) {
			return $this->redirectWithErrorMessage(
				'login',
				"No user with e-mail address '{$this->emailAddress}' exists.",
				$actx);
		}
		
		$ps = [
			"Someone requested a password reset for this e-mail address.\n".
			"If this wasn't you, ignore this message."
		];
		
		$pwrAction = $this->tokenModel->newTokenAction( [
			'half user ID' => $user['ID'],
			'action script' => 'reset-password',
			're-useable' => false,
		] );
		$ps[] = "Go here to set your password:\n".
			$actx->absoluteUrl("/reset-password?token={$pwrAction['token']}");
		
		$newMessage = new Swift_Message();
		$newMessage->setTo( $this->emailAddress );
		$newMessage->setFrom( $this->registry->getSwiftAddress('registration') );
		$newMessage->setSubject( "Password reset link" );
		$newMessage->setBody( implode("\n\n", $ps) );
		
		$this->mailer->send( $newMessage );
		
		return Nife_Util::httpResponse(200, "Password reset link sent to {$this->emailAddress}");
	}
}
