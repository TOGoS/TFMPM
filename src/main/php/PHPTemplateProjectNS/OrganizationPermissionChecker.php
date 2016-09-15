<?php

class PHPTemplateProjectNS_OrganizationPermissionChecker extends PHPTemplateProjectNS_Component
{
	public function itemsVisible(
		array $itemData,
		EarthIT_Schema_ResourceClass $rc,
		PHPTemplateProjectNS_ActionContext $actx,
		array &$notes
	) {
		$notes[] = get_class($this)."#itemsVisible doesn't know what to do with ".$rc->getName();
		return false;
	}

	public function preAuthorizeSimpleAction( $act, PHPTemplateProjectNS_ActionContext $actx, array &$notes ) {
		if(
			$act instanceof EarthIT_CMIPREST_RESTAction_SearchAction or
			$act instanceof EarthIT_CMIPREST_RESTAction_GetItemAction
		) {
			return EarthIT_CMIPREST_RESTActionAuthorizer::AUTHORIZED_IF_RESULTS_VISIBLE;
		}
		
		$notes[] = get_class($this)."#preAuthorizeSimpleAction doesn't know what to do with ".PHPTemplateProjectNS_Util::describe($act);
		return false;
	}
}
