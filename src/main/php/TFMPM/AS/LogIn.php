<?php

/*
 * (userid --)
 * Sets the password reset expiration time on the user record and
 * returns a redirect to the reset password page.
 */
class TFMPM_AS_LogIn extends TFMPM_Component implements TFMPM_AS_Action
{
	public function __invoke( TFMPM_AS_Context $asctx ) {
		$userId = $asctx->pop();
		$asctx->actx->setSessionVariable('userId',$userId);
	}
}
