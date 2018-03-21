<?php

/*
 * (a b -- a+b)
 * Here so we can test the script runner.
 */
class TFMPM_AS_Add extends TFMPM_Component implements TFMPM_AS_Action
{
	public function __invoke( TFMPM_AS_Context $asctx ) {
		$b = $asctx->pop();
		$a = $asctx->pop();
		$asctx->push( bcadd($a, $b) );
	}
}
