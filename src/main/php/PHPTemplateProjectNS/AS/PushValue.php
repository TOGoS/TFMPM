<?php

class PHPTemplateProjectNS_AS_PushValue implements PHPTemplateProjectNS_AS_Action
{
	protected $value;
	public function __construct( $value ) { $this->value = $value; }
	public function __invoke( PHPTemplateProjectNS_AS_Context $asctx ) {
		$asctx->dataStack[] = $this->value;
	}
}
