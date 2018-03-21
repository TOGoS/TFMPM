<?php

class TFMPM_PageAction_N2RGet extends TFMPM_PageAction
{
	protected $path;
	protected $req;
	
	public function __construct( TFMPM_Registry $reg, $path, TFMPM_Request $req ) {
		parent::__construct($reg);
		$this->path = $path;
		$this->req = $req;
	}
	
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		return true;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		// TODO: refactor N2RServer so it takes a $actx-like object.
		return $this->n2rServer->handleRequest($this->path);
	}
}
