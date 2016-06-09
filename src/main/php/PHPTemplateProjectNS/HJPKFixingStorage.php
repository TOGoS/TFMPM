<?php

/**
 * Storage class that fixes 'hashed JSON primary keys'
 * before saving.
 */
class PHPTemplateProjectNS_HJPKFixingStorage
extends PHPTemplateProjectNS_PassThroughStorage
{
	public function saveItems(array $itemData, EarthIT_Schema_ResourceClass $rc, array $options=array()) {
		$itemData = $this->hashUtil->fixItemsForSaving($itemData, $rc);
		return parent::saveItems( $itemData, $rc, $options );
	}
}
