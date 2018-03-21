<?php

class TFMPM_AS_Script implements TFMPM_AS_Action
{
	protected $actions;
	public function __construct( array $actions ) {
		$this->actions = $actions;
	}
	
	public static function parse( $script, $actionTable ) {
		$script = trim($script);
		$tokens = $script == '' ? [] : explode(' ',$script); // TODO: Use a proper tokenizer, like TOGVM's
		$actions = [];
		foreach( $tokens as $tok ) {
			if( isset($actionTable[$tok]) ) {
				$act = $actionTable[$tok];
			} else if( preg_match('/^\d+$/', $tok) ) {
				$act = new TFMPM_AS_PushValue($tok);
			} else if( preg_match('/^"([^\\"]+)"$/', $tok, $bif) ) {
				$act = new TFMPM_AS_PushValue($bif[1]);
			} else {
				throw new Exception("Unrecognized word: $tok");
			}
			$actions[] = $act;
		}
		return new TFMPM_AS_Script($actions);
	}
	
	public function __invoke( TFMPM_AS_Context $asctx ) {
		$res = null;
		$actions = $this->actions;
		$len = count($actions);
		while( $asctx->pc !== null and $asctx->pc >=0 and $asctx->pc < $len ) {
			$act = $actions[$asctx->pc];
			++$asctx->pc; // Action can override this if it needs to
			$res = call_user_func($act, $asctx);
		}
		return $res;
	}	
}
