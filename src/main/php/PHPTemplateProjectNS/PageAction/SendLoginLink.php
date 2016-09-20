<?php

class PHPTemplateProjectNS_PageAction_SendLoginLink extends PHPTemplateProjectNS_PageAction
{
	protected $emailAddress;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->emailAddress = $params['email-address'];
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$user = $this->storageHelper->getItem('user', ['e-mail address'=>$this->emailAddress]);
		if( $user === null ) {
			return $this->redirectWithErrorMessage(
				'login',
				"No user with e-mail address '{$this->emailAddress}' exists.",
				$actx);
		}
		
		$ps = [
			"Either you or someone requested a login link for this e-mail address.\n".
			"If this wasn't you, ignore this message."
		];
		
		$loginAction = $this->tokenModel->newTokenAction( [
			'half user ID' => $user['ID'],
			'action script' => 'log-in',
			're-useable' => true,
		] );
		$ps[] =
			"To log in without a password, use this link:\n".
			$actx->absoluteUrl("/do-token?token={$loginAction['token']}");
		
		$newMessage = new Swift_Message();
		$newMessage->setTo( $this->emailAddress );
		$newMessage->setFrom( $this->registry->getSwiftAddress('registration') );
		$newMessage->setSubject( "Login link" );
		$newMessage->setBody( implode("\n\n", $ps) );
		
		$this->mailer->send( $newMessage );
		
		return Nife_Util::httpResponse(200, "Login link sent to {$this->emailAddress}");
	}
}
