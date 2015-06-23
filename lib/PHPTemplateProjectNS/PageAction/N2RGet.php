<?php

class PHPTemplateProjectNS_PageAction_N2RGet extends PHPTemplateProjectNS_PageAction
{
	protected $path;
	protected $req;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $path, PHPTemplateProjectNS_Request $req ) {
		parent::__construct($reg);
		$this->path = $path;
		$this->req = $req;
	}
	
	public function isAllowed( PHPTemplateProjectNS_ActionContext $actx, &$status, array &$notes=[] ) {
		return true;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		// TODO: refactor N2RServer so it takes a $actx-like object.
		return $this->n2rServer->handleRequest($this->path);
	}
}
