<?php

class TFMPM_PassThroughStorage
extends TFMPM_Component
implements EarthIT_CMIPREST_Storage
{
	protected $backingStorage;
	
	public function __construct( TFMPM_Registry $reg, EarthIT_CMIPREST_Storage $backing ) {
		parent::__construct($reg);
		$this->backingStorage = $backing;
	}
	
	/** @override */
	public function searchItems( EarthIT_Storage_Search $search, array $options=array() ) {
		return $this->backingStorage->searchItems( $search, $options );
	}

	/** @override */
	public function johnlySearchItems(
		EarthIT_Storage_Search $search,
		array $johnBranches,
		array $options=array()
	) {
		return $this->backingStorage->johnlySearchItems( $search, $johnBranches, $options );
	}
	
	/** @override */
	public function deleteItems( EarthIT_Schema_ResourceClass $rc, EarthIT_Storage_ItemFilter $filter ) {
		return $this->backingStorage->deleteItems( $rc, $filter );
	}
	
	/** @override */
	public function updateItems(
		array $updatedFieldValues, EarthIT_Schema_ResourceClass $rc,
		EarthIT_Storage_ItemFilter $filter, array $options=array()
	) {
		return $this->backingStorage->updateItems( $updatedFieldValues, $rc, $filter, $options );
	}
	
	/** @override */
	public function saveItems(array $itemData, EarthIT_Schema_ResourceClass $rc, array $options=array()) {
		return $this->backingStorage->saveItems( $itemData, $rc, $options );
	}

	use TFMPM_StorageBonub;
}
