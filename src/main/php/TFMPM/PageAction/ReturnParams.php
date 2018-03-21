<?php

class TFMPM_PageAction_ReturnParams
extends TFMPM_PageAction
{
	protected $params;
	
	public function __construct( TFMPM_Registry $reg, $params ) {
		parent::__construct($reg);
		$this->params = $params;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		return $this->jsonResponse(200, $this->params);
	}
}
