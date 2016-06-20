<?php

class PHPTemplateProjectNS_PageAction_DoActionScript extends PHPTemplateProjectNS_PageAction
{
	public function isAllowed( PHPTemplateProjectNS_ActionContext $actx, &$status, array &$notes=[] ) {
		$status = 403;
		$notes[] = "Too dangerous!";
		return false;
	}
	
	protected $scriptText;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $scriptText ) {
		parent::__construct($reg);
		$this->scriptText = $scriptText;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$actTab = new PHPTemplateProjectNS_AS_VeryActionTable($this->registry);
		$script = PHPTemplateProjectNS_AS_Script::parse($this->scriptText, $actTab);
		$asctx = new PHPTemplateProjectNS_AS_Context( $actx );
		$res = $script->__invoke( $asctx );
		if( $res === null ) {
			$res = $this->jsonResponse( 200, $asctx->peek() );
		}
		return $res;
	}
}
