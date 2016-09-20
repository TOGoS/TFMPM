<?php

class PHPTemplateProjectNS_OrganizationModel extends PHPTemplateProjectNS_Component
{
	// Hey, hey!  These values are somewhat magical, in that they can be used as
	// "<org A> is <rlxn> <org B>", and (except for RLXN_NONE) "applies <rlxn> attachment point"
	// (that's user role permission columns).
	const RLXN_SAME  = 'at';
	const RLXN_ABOVE = 'above';
	const RLXN_BELOW = 'below';
	const RLXN_NONE  = 'unrelated-to';
	
	const ORGANIZATION_RC_NAME = 'organization';
	const ORGANIZATION_TABLE_NAME = 'phptemplateprojectdatabasenamespace.organization';

	/** Set of IDs that we want to cache next time we do an organization-fetching query */
	protected $precacheOrgIds = array();
	/** Org ID => schema-form organization record */
	protected $orgCache = array();

	public function precacheOrgs( array $orgIds ) {
		foreach( $orgIds as $orgId ) $this->precacheOrgIds[$orgId] = $orgId;
	}
	
	public function cachePrecacheOrgs() {
		if( empty($this->precacheOrgIds) ) return;
		
		$orgs = $this->storageHelper->queryItems(
			self::ORGANIZATION_RC_NAME,
			"SELECT * FROM {orgTable}\n".
			"WHERE id IN {orgIds}\n".
			"   OR parentid IN {orgIds}\n".
			"   OR id IN (SELECT parentid FROM {orgTable} WHERE id IN {orgIds})\n",
			[
				'orgIds' => $this->precacheOrgIds,
				'orgTable' => new EarthIT_DBC_SQLIdentifier(self::ORGANIZATION_TABLE_NAME),
			]
		);
		foreach( $orgs as $org ) $this->orgCache[$org['ID']] = $org;
		
		$this->precacheOrgIds = array();
	}
	
	/**
	 * Returns schema-form organization record.
	 * Will use an internal cache and also fetch related organization records.
	 */
	public function getOrganization($orgId) {
		if( array_key_exists($orgId, $this->orgCache) ) return $this->orgCache[$orgId];
		
		$this->precacheOrgIds[$orgId] = $orgId;
		$this->cachePrecacheOrgs();
		if( !isset($this->orgCache[$orgId]) ) $this->orgCache[$orgId] = null;
		
		return $this->orgCache[$orgId];
	}
	
	/** What is $orgA's relationship to $orgB? */
	protected function _getOrganizationRelationship( $orgAId, $orgBId ) {
		if( $orgAId == $orgBId ) return self::RLXN_SAME;
		
		$orgId = $orgAId;
		while( $orgId !== null ) {
			$org = $this->getOrganization($orgId);
			$orgId = $org['parent ID'];
			if( $orgId == $orgBId ) return self::RLXN_BELOW;
		}
		
		$orgId = $orgBId;
		while( $orgId !== null ) {
			$org = $this->getOrganization($orgId);
			$orgId = $org['parent ID'];
			if( $orgId == $orgAId ) return self::RLXN_ABOVE;
		}
		
		return self::RLXN_NONE; // They could be cousins or something, but we don't care about that
	}

	protected $orgRlxnCache = array();
	public function getOrganizationRelationship( $orgAId, $orgBId ) {
		$k = "{$orgAId}-{$orgBId}";
		if( isset($this->orgRlxnCache[$k]) ) return $this->orgRlxnCache[$k];
		return $this->orgRlxnCache[$k] = $this->_getOrganizationRelationship($orgAId, $orgBId);
	}
	
	public function clearCache() {
		$this->orgCache = array();
		$this->orgRlxnCache = array();
	}
}
