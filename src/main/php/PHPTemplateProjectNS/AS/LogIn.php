<?php

/*
 * (userid --)
 * Sets the password reset expiration time on the user record and
 * returns a redirect to the reset password page.
 */
class PHPTemplateProjectNS_AS_LogIn extends PHPTemplateProjectNS_Component implements PHPTemplateProjectNS_AS_Action
{
	public function __invoke( PHPTemplateProjectNS_AS_Context $asctx ) {
		$userId = $asctx->pop();
		$asctx->actx->setSessionVariable('userId',$userId);
	}
}
