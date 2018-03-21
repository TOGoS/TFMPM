<?php

class TFMPM_PageAction_DoToken extends TFMPM_PageAction
{
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		// You're always allowed to try!
		// Might get a 409 or something if it's expired
		return true;
	}
	
	protected $token;
	protected $forwardUrl;
	
	public function __construct( TFMPM_Registry $reg, $token, $forwardUrl ) {
		parent::__construct($reg);
		$this->token = $token;
		$this->forwardUrl = $forwardUrl;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$notes = [];
		$tokenAction = $this->tokenModel->getTokenActionIfValid( $this->token, $notes, true );
		if( $tokenAction === null ) {
			// TODO: Make a nice error page
			throw new Exception("Token invalid!\n".implode("\n", $notes));
		}
		
		$actTab = new TFMPM_AS_VeryActionTable($this->registry);
		$script = TFMPM_AS_Script::parse($tokenAction['action script'], $actTab);
		$asctx = new TFMPM_AS_Context( $actx );
		$asctx->push( $tokenAction['half user ID'] );
		$res = $script->__invoke( $asctx );
		if( $res === null ) {
			return $this->redirect( 303, $actx->relativeUrl('/') );
		}
		return $res;
	}
}
