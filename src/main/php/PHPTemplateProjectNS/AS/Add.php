<?php

/*
 * (a b -- a+b)
 * Here so we can test the script runner.
 */
class PHPTemplateProjectNS_AS_Add extends PHPTemplateProjectNS_Component implements PHPTemplateProjectNS_AS_Action
{
	public function __invoke( PHPTemplateProjectNS_AS_Context $asctx ) {
		$b = $asctx->pop();
		$a = $asctx->pop();
		$asctx->push( bcadd($a, $b) );
	}
}
