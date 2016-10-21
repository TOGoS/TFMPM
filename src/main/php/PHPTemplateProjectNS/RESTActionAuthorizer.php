<?php

class PHPTemplateProjectNS_RESTActionAuthorizer
extends EarthIT_CMIPREST_RESTActionAuthorizer_DefaultRESTActionAuthorizer
{
	protected $registry;
	
	use PHPTemplateProjectNS_ComponentGears;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg ) {
		$this->registry = $reg;
	}
	
	/** @override */
	public function preAuthorizeSimpleAction( EarthIT_CMIPREST_RESTAction $act, $ctx, array &$explanation ) {
		if( $this->registry->getConfig("auth/bypass") ) {
			$explanation[] = "Normal authorization rules bypassed as per auth/bypass config setting.";
			return true;
		}
		
		$orgCheckerSays = $this->organizationPermissionChecker->preAuthorizeSimpleAction($act, $ctx, $explanation);
		if( $orgCheckerSays === true ) return true;
		
		// Could use PermissionUtil::max to make things more clearer,
		// but since there's only 3 possible values we can be cleverer
		// (and probably slightly more efficient).
		// Will want to abstract this if there are more permission checkers going at it.
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_SearchAction ) return self::AUTHORIZED_IF_RESULTS_VISIBLE;
		
		return $orgCheckerSays;
	}
	
	/** @override */
	protected function itemVisible( $item, EarthIT_Schema_ResourceClass $rc, $ctx, array &$explanation ) {
		if( $this->registry->getConfig("auth/bypass") ) {
			$explanation[] = "Normal authorization rules bypassed as per auth/bypass config setting.";
			return true;
		}
		
		if( $rc->membersArePublic() ) return true;
		
		if( $this->organizationPermissionChecker->itemsVisible(array($item), $rc, $ctx, $explanation) ) {
			return true;
		}
		
		// Users are allowed to see their own user record,
		// regardless of what organizationpermissionchecker says
		if( $rc->getName() === 'user' && $item['ID'] == $ctx->getLoggedInUserId() ) return true;
		
		return false;
	}
	
	/** @override */
	public function sudoAllowed( $userId, $ctx, array &$explanation ) {
		if( $this->registry->getConfig("auth/bypass") ) {
			$explanation[] = "Normal authorization rules bypassed as per auth/bypass config setting.";
			return true;
		}
		
		return parent::sudoAllowed($userId, $ctx, $explanation);
	}
}
