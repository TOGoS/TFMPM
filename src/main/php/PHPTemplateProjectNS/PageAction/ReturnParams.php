<?php

class PHPTemplateProjectNS_PageAction_ReturnParams
extends PHPTemplateProjectNS_PageAction
{
	protected $params;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $params ) {
		parent::__construct($reg);
		$this->params = $params;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		return $this->jsonResponse(200, $this->params);
	}
}
