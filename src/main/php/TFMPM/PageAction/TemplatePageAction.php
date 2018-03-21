<?php

abstract class TFMPM_PageAction_TemplatePageAction extends TFMPM_PageAction
{
	protected function getStatusCode() { return 200; }
	protected function getHeaders() { return ['content-type'=>'text/html; charset=utf-8']; }
	protected abstract function getTemplateName();
	protected abstract function getTemplateParameters();
	
	protected $errorMessageId; // Set in constructor if your pageaction has error messages
	
	public function __construct( TFMPM_Registry $reg, $errorMessageId=null ) {
		parent::__construct($reg);
		$this->errorMessageId = $errorMessageId;
	}

	protected function contextTemplateVars( TFMPM_ActionContext $actx, array &$into=array() ) {
		parent::contextTemplateVars( $actx, $into );
		$errorMessage = $this->getErrorMessage($this->errorMessageId, $actx);
		$into['errorMessages'] = $errorMessage ? explode("\x1e", $errorMessage) : [];
		return $into;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		return $this->templateResponse(
			$this->getStatusCode(),
			$this->getTemplateName(),
			$this->getTemplateParameters() + $this->contextTemplateVars($actx),
			$this->getHeaders()
		);
	}
}
