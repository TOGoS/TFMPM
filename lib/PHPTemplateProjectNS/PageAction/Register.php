<?php

class PHPTemplateProjectNS_PageAction_Register extends PHPTemplateProjectNS_PageAction
{
	protected $name;
	protected $emailAddress;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, array $params ) {
		parent::__construct($reg);
		$this->name = $params['name'];
		$this->emailAddress = $params['e-mail-address'];
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$newMessage = new Swift_Message();
		try {
			$newMessage->setTo( $this->emailAddress );
		} catch( Swift_RfcComplianceException $e ) {
			return $this->redirectWithErrorMessage(
				'register',
				["The supplied e-mail address, '{$this->emailAddress}', is invalid",$e->getMessage()],
				$actx);
		}

		$password = "Asdf1234";
		
		$okay = false;
		$this->storageHelper->beginTransaction();
		try {
			$existingUser = $this->storageHelper->getItem('user', ['e-mail address'=>$this->emailAddress]);
			if( $existingUser !== null ) {
				return $this->redirectWithErrorMessage(
					'register',
					"The supplied e-mail address, '{$this->emailAddress}', is already taken.",
					$actx);
			}
			$passhash = $this->userModel->hashPassword($password);
			$user = [
				'username' => $this->name,
				'e-mail address' => $this->emailAddress,
				'passhash' => $passhash
			];
			$this->storageHelper->insertNewItem('user', $user);
			
			$okay = true;
		} finally {
			$this->storageHelper->endTransaction($okay);
		}

		$newMessage->setFrom( array('fake-registrator@example.org' => 'PHP Template Project') );
		$newMessage->setSubject( "Thank you for registering!" );
		$newMessage->setBody(
			"Thanks for registering, {$this->name}!\n".
			"Your password is $password." );
		
		$this->mailer->send( $newMessage );
		
		return Nife_Util::httpResponse(200, 'Confirmation message sent!');
	}
}
