<?php

class TFMPM_PageAction_DoActionScript extends TFMPM_PageAction
{
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		$status = 403;
		$notes[] = "Too dangerous!";
		return false;
	}
	
	protected $scriptText;
	
	public function __construct( TFMPM_Registry $reg, $scriptText ) {
		parent::__construct($reg);
		$this->scriptText = $scriptText;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$actTab = new TFMPM_AS_VeryActionTable($this->registry);
		$script = TFMPM_AS_Script::parse($this->scriptText, $actTab);
		$asctx = new TFMPM_AS_Context( $actx );
		$res = $script->__invoke( $asctx );
		if( $res === null ) {
			$res = $this->jsonResponse( 200, $asctx->peek() );
		}
		return $res;
	}
}
