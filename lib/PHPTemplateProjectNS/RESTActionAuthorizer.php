<?php

class PHPTemplateProjectNS_RESTActionAuthorizer
extends EarthIT_CMIPREST_RESTActionAuthorizer_DefaultRESTActionAuthorizer
{
	protected $registry;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg ) {
		$this->registry = $reg;
	}
	
	/** @override */
	public function preAuthorizeSimpleAction( EarthIT_CMIPREST_RESTAction $act, $ctx, array &$explanation ) {
		if( $this->registry->getConfig("auth/bypass") ) {
			$explanation[] = "Normal uuthorization rules bypassed as per auth/bypass config setting.";
			return true;
		}
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_SearchAction ) return self::AUTHORIZED_IF_RESULTS_VISIBLE;
		
		// Anything not explicitly allowed is disallowed.
		return false;
	}
	
	/** @override */
	public function itemsVisible( array $itemData, EarthIT_Schema_ResourceClass $rc, $ctx, array &$explanation ) {
		if( $this->registry->getConfig("auth/bypass") ) {
			$explanation[] = "Normal uuthorization rules bypassed as per auth/bypass config setting.";
			return true;
		}
		
		if( $rc->membersArePublic() ) return true;
		
		$visible = true;
		
		// Users are allowed to see records that are linked directly to
		// their user ID (this is not a good general rule; it's here for
		// demonstrative purposes)
		$userIdFieldNames = [];
		if( $rc->getName() === 'user' ) $userIdFieldNames[] = 'ID';
		foreach( $rc->getReferences() as $ref ) {
			if( $ref->getTargetClassName() === 'user' ) {
				foreach( $ref->getOriginFieldNames() as $fn ) $userIdFieldNames[] = $fn;
			}
		}
		
		foreach( $itemData as $item ) {
			$itemVisible = false;
			foreach( $userIdFieldNames as $fn ) {
				if( $item[$fn] === $ctx->getLoggedInUserId() ) {
					$itemVisible = true;
				}
			}
			$visible &= $itemVisible;
		}
	
		return $visible;
	}
}
