<?php

class TFMPM_OrganizationPermissionChecker extends TFMPM_Component
{
	protected $uoaCache = array();
	
	public function getUserOrganizationAttachments( $userId ) {
		if( $userId === null ) $userId = '0';
		
		if( isset($this->uoaCache[$userId]) ) return $this->uoaCache[$userId];

		$roleSelects =
			"\tur.id AS roleid, ur.name AS rolename,\n".
			"\turp.resourceclassid,\n".
			"\turp.actionclassname,\n".
			"\turp.appliessystemwide,\n".
			"\turp.appliesatattachmentpoint,\n".
			"\turp.appliesaboveattachmentpoint,\n".
			"\turp.appliesbelowattachmentpoint,\n".
			"\trc.name AS resourceclassname\n";

		$rose = $this->storageHelper->queryRows(
			"SELECT\n".
			"\tuoa.userid, uoa.organizationid,\n".
			$roleSelects.
			"FROM tfmpm.userorganizationattachment AS uoa\n".
			"JOIN tfmpm.userrole AS ur ON ur.id = uoa.roleid\n".
			"JOIN tfmpm.userrolepermission AS urp ON urp.roleid = uoa.roleid\n".
			"JOIN tfmpm.resourceclass AS rc ON rc.id = urp.resourceclassid\n".
			"WHERE uoa.userid IN {userIds}\n".
			"\n".
			"UNION\n".
			"\n".
			"SELECT\n".
			"\t0 AS userid, 51 AS organizationid,\n".
			$roleSelects.
			"FROM tfmpm.defaultuserrole AS dr\n".
			"JOIN tfmpm.userrole AS ur ON ur.id = dr.roleid\n".
			"JOIN tfmpm.userrolepermission AS urp ON urp.roleid = dr.roleid\n".
			"JOIN tfmpm.resourceclass AS rc ON rc.id = urp.resourceclassid\n".
			($userId === '0' ? "WHERE dr.requirelogin = FALSE\n" : ""),
			['userIds' => [$userId]]
		);
		$uoas = [];
		foreach( $rose as $rouse ) {
			$uoaId = $rouse['userid'].'-'.$rouse['roleid'].'-'.$rouse['organizationid'];
			if( !isset($uoas[$uoaId]) ) $uoas[$uoaId] = [
				'user ID' => $rouse['userid'],
				'organization ID' => $rouse['organizationid'],
				'role' => [
					'ID' => $rouse['roleid'],
					'name' => $rouse['rolename'],
					'user role permissions' => [],
				]
			];
			$urpId = $rouse['roleid'].'-'.$rouse['resourceclassid'].'-'.$rouse['actionclassname'];
			$uoas[$uoaId]['user role permissions'][$urpId] = [
				'action class name' => $rouse['actionclassname'],
				'resource class ID' => $rouse['resourceclassid'],
				'resource class name' => $rouse['resourceclassname'],
				'applies system-wide' => (bool)$rouse['appliessystemwide'],
				'applies at attachment point' => (bool)$rouse['appliesatattachmentpoint'],
				'applies above attachment point' => (bool)$rouse['appliesaboveattachmentpoint'],
				'applies below attachment point' => (bool)$rouse['appliesbelowattachmentpoint'],
			];
		}
		
		return $this->uoaCache[$userId] = $uoas;
	}
		
	public function userCanDoBasicActionOnObjectInOrg( $userId, $actionName, $objectOrgId, $objectRcName, &$notes=[] ) {
		$uoas = $this->getUserOrganizationAttachments( $userId );
		
		foreach( $uoas as $uoa ) {
			foreach( $uoa['user role permissions'] as $urp ) {
				if( $urp['resource class name'] == $objectRcName and $urp['action class name'] == $actionName ) {
					$userOrgId = $uoa['organization ID'];
					$orgRlxn = $this->organizationModel->getOrganizationRelationship( $objectOrgId, $userOrgId );
					$notes[] = "org $objectOrgId is $orgRlxn org $userOrgId";
					if( $orgRlxn !== TFMPM_OrganizationModel::RLXN_NONE ) {
						$checkApplicabilityFieldName = "applies {$orgRlxn} attachment point";
						if( $urp[$checkApplicabilityFieldName] ) {
							$notes[] = "User has $actionName permission on $objectRcName records $orgRlxn their attachment point at $userOrgId";
							return true;
						}
					}
				}
			}
		}
		
		return false;
	}
	
	/** Array of $rcName => $itemId => $itemData */
	protected $itemCache = array();
	protected function getItem( $itemId, $rcName ) {
		if( isset($this->itemCache[$rcName]) and array_key_exists($itemId, $this->itemCache[$rcName]) ) {
			return $this->itemCache[$rcName][$itemId];
		}
		
		return $this->itemCache[$rcName][$itemId] = $this->storageHelper->getItemById($rcName, $itemId);
	}
	
	/**
	 * @param $itemOrItemId if scalar, this is treated as an item ID; if an array, it is the item's field values
	 * @param $rcName name of the item's resource class
	 * @return a set (k=v) of owning organization IDs
	 */
	public function getOwningOrganizationIds( $itemOrItemId, $rcName ) {
		if( is_array($itemOrItemId) ) {
			$itemId = null;
			$item   = $itemOrItemId;
		} else if( is_scalar($itemOrItemId) ) {
			$item   = null;
			$itemId = $itemOrItemId;
		} else {
			throw new Exception(
				"Given ".TFMPM_Util::describe($itemOrItemId)." instead of an item or an item ID ".
				"when looking up owning organization IDs for a {$rcName}");
		}
		
		// We could make this be a more generic 'get owner IDs'
		// that returns all IDs of a set of RCs, not just organization.
		// e.g. in case the user record itself owns something.
		// Which isn't so far fetched
		// (but then we could accomplish the same thing by just giving the user their own organization)
		
		if( $rcName == $this->organizationModel->organizationResourceClassName ) {
			if( $itemId === null ) $itemId = EarthIT_Storage_Util::itemId($item, $this->rc($rcName));
			return array($itemId=>$itemId);
		}
		$rc = $this->rc($rcName);
		$owningOrgIds = array();
		foreach( $rc->getReferences() as $ref ) {
			if( $ref->getFirstPropertyValue("http://ns.nuke24.net/Schema/Application/indicatesOwner") ) {
				if( $item === null ) $item = $this->getItem($itemId, $rcName);
				if( $item === null ) {
					throw new Exception("Oh no, $rcName $itemId is null!");
				}
				
				$refFieldNames = $ref->getOriginFieldNames();
				$targetIdParts = [];
				$missingReferenceFieldNames = [];
				foreach( $refFieldNames as $rfn ) {
					$targetIdPart = $item[$rfn];
					if( empty($targetIdPart) ) $missingReferenceFieldNames[] = $rfn;
					$targetIdParts[] = $targetIdPart;
				}
				if( $missingReferenceFieldNames ) {
					$notes[] = "$itemId ($rcName) has no ".$ref->getName()."; ".implode(',',$missingReferenceFieldNames)." are null";
				} else {
					$targetId = implode('-', $targetIdParts);
					foreach( $this->getOwningOrganizationIds($targetId, $ref->getTargetClassName()) as $id ) {
						$owningOrgIds[$id] = $id;
					}
				}
			}
			// TODO: May eventually also need to go through and check all tables for ownees
			// if we want to support that, which will be a pain.
		}
		
		return $owningOrgIds;
	}
	
	/**
	 * @return an 'ownership changes' array(
	 *   remove from organization IDs => set of organization IDs an item is being removed from
	 *   add to organization IDs      => set of organization IDs an item is being added to
	 * )
	 */
	protected function figureOwnershipChanges( $oldItem, $newItem, $isPatch, $rcName ) {
		if( is_scalar($oldItem) ) {
			$oldItem = $this->getItem($oldItem, $rcName);
		}
		if( $isPatch && $oldItem !== null ) $newItem += $oldItem;
		$oldOrgIds = $oldItem === null ? array() : $this->getOwningOrganizationIds( $oldItem, $rcName );
		$newOrgIds = $newItem === null ? array() : $this->getOwningOrganizationIds( $newItem, $rcName );
		
		$removals = array();
		$additions = array();
		foreach( $oldOrgIds as $orgId ) {
			if( !isset($newOrgIds[$orgId]) ) $removals[$orgId]  = $orgId;
		}
		foreach( $newOrgIds as $orgId ) {
			if( !isset($oldOrgIds[$orgId]) ) $additions[$orgId] = $orgId;
		}
		return array(
			'remove from organization IDs' => $removals,
			'add to organization IDs' => $additions,
		);
	}
	
	public function userCanDoBasicActionOnObject( $userId, $actionName, $objectOrObjectId, $objectRcName, array &$notes=[] ) {
		$uoas = $this->getUserOrganizationAttachments( $userId );
		
		// Check for any system-wide permissions first,
		// since we could then skip organization queries.
		$anyNonSystemWidePermissions = false;
		foreach( $uoas as $uoa ) {
			foreach( $uoa['user role permissions'] as $urp ) {
				if( $urp['resource class name'] == $objectRcName and $urp['action class name'] == $actionName ) {
					if( $urp['applies system-wide'] ) {
						$notes[] = "User has system-wide permission to {$actionName} {$objectRcName} records";
						return true;
					} else {
						// Will have to do organization structure checks. ;(
						$anyNonSystemWidePermissions = true;
					}
				}
			}
		}
		
		if( !$anyNonSystemWidePermissions ) {
			$notes[] = "User has no organization permissions to $actionName $objectRcName records";
			return false;
		}
		
		$objectOrgIds = $this->getOwningOrganizationIds($objectOrObjectId, $objectRcName);
		foreach( $objectOrgIds as $objectOrgId ) {
			$notes[] = "Checking for '$actionName' permission on '$objectRcName' records in org '$objectOrgId'";
			if( $this->userCanDoBasicActionOnObjectInOrg($userId, $actionName, $objectOrgId, $objectRcName, $notes) ) return true;
		}
		
		return false;
	}

	protected function userCanChangeObjectOwnership( $userId, $ownershipChanges, $rcName, array &$notes=array() ) {
		$allowed = 1;
		foreach( $ownershipChanges['add to organization IDs'] as $orgId ) {
			$allowed &= $this->userCanDoBasicActionOnObjectInOrg($userId, 'move-to', $orgId, $rcName, $notes);
		}
		foreach( $ownershipChanges['remove from organization IDs'] as $orgId ) {
			$allowed &= $this->userCanDoBasicActionOnObjectInOrg($userId, 'move-from', $orgId, $rcName, $notes);
		}
		return (bool)$allowed;
	}
	
	public function itemsVisible(
		array $itemData,
		EarthIT_Schema_ResourceClass $itemRc,
		TFMPM_ActionContext $actx,
		array &$notes
	) {
		$userId = $actx->getLoggedInUserId();
		if( $userId === null ) {
			$notes[] = "Not logged in";
		}
		
		foreach( $itemData as $item ) {
			$itemId = EarthIT_Storage_Util::itemId($item, $itemRc);
			$itemRcName = $itemRc->getName();
			$notes[] = "Checking 'read' permission on ".($itemId ? "$itemRcName $itemId" : "some $itemRcName")."...";
			if( !$this->userCanDoBasicActionOnObject($userId, 'read', $item, $itemRcName, $notes) ) return false;
		}
		
		$notes[] = "No items unreadable by $userId";
		return true;
	}

	/**
	 * Authorize simplified actions
	 * that have already been simplified/translated
	 */
	public function preAuthorizeSimplerAction( $act, TFMPM_ActionContext $actx, array &$notes ) {
		$userId = $actx->getLoggedInUserId();
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_DeleteItemAction ) {
			$itemId = $act->getItemId();
			$rcName = $act->getResourceClass()->getName();
			$item = $this->getItem($itemId, $rcName);
			if( $item === null ) {
				$notes[] = "$rcName $itemId doesn't exist anyway, so sure, you can 'delete' it.";
				return true;
			}
			return $this->userCanDoBasicActionOnObject(
				$userId, 'delete',
				$itemId, $rcName, $notes);
		}
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_PatchItemAction ) {
			$ownershipChanges = $this->figureOwnershipChanges(
				$act->getItemId(), $act->getItemData(), true, $act->getResourceClass()->getName() );
			return
				$this->userCanChangeObjectOwnership( $userId, $ownershipChanges, $act->getResourceClass()->getName(), $notes ) &&
				$this->userCanDoBasicActionOnObject(
					$userId, 'update',
					$act->getItemId(), $act->getResourceClass()->getName(),
					$notes);
		}
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_PostItemAction ) {
			$ownershipChanges = $this->figureOwnershipChanges(
				null, $act->getItemData(), false, $act->getResourceClass()->getName() );
			foreach( $ownershipChanges['add to organization IDs'] as $addToOrgId ) {
				if( !$this->userCanDoBasicActionOnObjectInOrg(
					$userId, 'create', $addToOrgId, $act->getResourceClass()->getName(), $notes
				) ) return false;
			}
			return $this->userCanDoBasicActionOnObject(
				$userId, 'create',
				$act->getItemData(), $act->getResourceClass()->getName(),
				$notes);
		}
		
		$notes[] = get_class($this)."#preAuthorizeSimplerAction doesn't know what to do with ".TFMPM_Util::describe($act);
		return false;
	}

	public function preAuthorizeSimpleAction( $act, TFMPM_ActionContext $actx, array &$notes ) {
		if(
			$act instanceof EarthIT_CMIPREST_RESTAction_SearchAction or
			$act instanceof EarthIT_CMIPREST_RESTAction_GetItemAction
		) {
			return EarthIT_CMIPREST_RESTActionAuthorizer::AUTHORIZED_IF_RESULTS_VISIBLE;
		}
		
		// POSTs to existing items are actually treated as PATCHes, so translate those:
		if( $act instanceof EarthIT_CMIPREST_RESTAction_PostItemAction ) {
			// Is it actually a patch?  Then translate it to one.
			$item = $act->getItemData();
			$rc = $act->getResourceClass();
			$itemId = EarthIT_Storage_Util::itemId($item, $rc);
			if(
				$itemId !== null and
				($item = $this->storageHelper->getItemById($act->getResourceClass(), $itemId)) !== null
			) {
				// It's a patch!
				$act = new EarthIT_CMIPREST_RESTAction_PatchItemAction($rc, $itemId, $item, $act->getResultAssembler());
			}
		}
		
		if( $act instanceof EarthIT_CMIPREST_RESTAction_PutItemAction ) {
			return $this->preAuthorizeSimplerAction(
				new EarthIT_CMIPREST_RESTAction_DeleteItemAction($act->getResourceClass(), $act->getItemId(), $act->getResultAssembler()),
				$actx, $notes
			) && $this->preAuthorizeSimplerAction(
				new EarthIT_CMIPREST_RESTAction_PostItemAction($act->getResourceClass(), $act->getItemData(), $act->getResultAssembler()),
				$actx, $notes
			);
		}
		
		return $this->preAuthorizeSimplerAction( $act, $actx, $notes );
	}
}
