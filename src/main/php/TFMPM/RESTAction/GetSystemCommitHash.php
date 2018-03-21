<?php

class TFMPM_RESTAction_GetSystemCommitHash extends TFMPM_SpecialAction
{
	public function __invoke(TFMPM_ActionContext $actx) {
		return Nife_Util::httpResponse(200, `git rev-parse HEAD`);
	}
}
