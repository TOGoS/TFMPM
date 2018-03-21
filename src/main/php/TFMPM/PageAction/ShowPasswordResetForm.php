<?php

class TFMPM_PageAction_ShowPasswordResetForm extends TFMPM_PageAction_TemplatePageAction
{
	protected $token;
	public function __construct( TFMPM_Registry $reg, $token ) {
		parent::__construct($reg);
		$this->token = $token;
	}
	
	public function getTemplateName() { return 'reset-password'; }
	public function getTemplateParameters() {
		if( empty($this->token) ) throw new Exception("No token given");
		// TODO: Show an error message if token is not valid
		return ['token' => $this->token];
	}
}
