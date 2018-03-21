<?php

interface TFMPM_AS_Action extends TOGoS_Action
{
	public function __invoke( TFMPM_AS_Context $asctx );
}
