<?php

class PHPTemplateProjectNS_PageAction_RegisterEmailAddress extends PHPTemplateProjectNS_PageAction
{
	protected $emailAddress;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $emailAddress ) {
		parent::__construct($reg);
		$this->emailAddress = $emailAddress;
	}
	
	public function __invoke() {
		$newMessage = new Swift_Message();
		$newMessage->setTo( $this->emailAddress );
		$newMessage->setFrom( array('fake-registrator@example.org' => 'PHP Template Project') );
		$newMessage->setSubject( "Thank you for registering!" );
		$newMessage->setBody( "You clicked a button on a form." );
		
		$this->mailer->send( $newMessage );
		
		return Nife_Util::httpResponse(200, 'Confirmation message sent!');
	}
}
