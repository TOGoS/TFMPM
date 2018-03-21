<?php

class TFMPM_AS_PushValue implements TFMPM_AS_Action
{
	protected $value;
	public function __construct( $value ) { $this->value = $value; }
	public function __invoke( TFMPM_AS_Context $asctx ) {
		$asctx->dataStack[] = $this->value;
	}
}
