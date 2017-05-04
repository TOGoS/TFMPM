<?php

class PHPTemplateProjectNS_RESTAction_GetSystemCommitHash extends PHPTemplateProjectNS_SpecialAction
{
	public function __invoke(PHPTemplateProjectNS_ActionContext $actx) {
		return Nife_Util::httpResponse(200, `git rev-parse HEAD`);
	}
}
