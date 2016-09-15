<?php

class PHPTemplateProjectNS_OrganizationPermissionChecker extends PHPTemplateProjectNS_Component
{
	public function isActionAllowed( $act, PHPTemplateProjectNS_ActionContext $actx, array &$notes ) {
		$notes[] = "I don't know what a ".PHPTemplateProjectNS_Util::describe($act)." is";;
		return false;
	}
}