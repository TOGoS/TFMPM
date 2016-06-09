<?php

// Bonus?

/**
 * Implements some EarthIT_CMIPREST_Storage methods
 * in terms of other, more general, methods.
 */
trait PHPTemplateProjectNS_StorageBonub
{
	/** @override */
	public function postItem( EarthIT_Schema_ResourceClass $rc, array $itemData ) {
		return EarthIT_CMIPREST_Util::postItem( $this, $rc, $itemData );
	}
	
	/** @override */
	public function putItem( EarthIT_Schema_ResourceClass $rc, $itemId, array $itemData ) {
		return EarthIT_CMIPREST_Util::putItem( $this, $rc, $itemId, $itemData );
	}
	
	/** @override */
	public function patchItem( EarthIT_Schema_ResourceClass $rc, $itemId, array $itemData ) {
		return EarthIT_CMIPREST_Util::patchItem( $this, $rc, $itemId, $itemData );
	}
	
	/** @override */
	public function deleteItem( EarthIT_Schema_ResourceClass $rc, $itemId ) {
		$this->deleteItems($rc, EarthIT_Storage_ItemFilters::byId($itemId, $rc));
	}
}
