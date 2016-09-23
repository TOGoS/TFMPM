<?php

class PHPTemplateProjectNS_OrganizationPermissionCheckerTest extends PHPTemplateProjectNS_TestCase
{
	// For these tests wee'll assume things are set up as by
	// build/db/test-data/1003-acl-test-data.sql
	
	protected function makeAction( $meth, $path, $qs='', $contobj=null ) {
		$contentBlob = $contobj ? new EarthIT_JSON_PrettyPrintedJSONBlob($contobj) : null;
		return $this->router->apiRequestToAction($meth, $path, $qs, $contentBlob);
	}
	
	/**
	 * A CMIPREST_RESTer that only uses our OrganizationPermissionChecker
	 * for authorizing.
	 */
	protected $testRester;
	
	public function setUp() {
		$this->testRester = new EarthIT_CMIPREST_RESTer( array(
			'storage' => $this->storage,
			'schema' => $this->schema,
			'keyByIds' => true,
			'authorizer' => $this->organizationPermissionChecker
		));
	}
	
	protected function isAllowed( $act, $actx, array &$notes ) {
		// We determine this by doing the action, ha ha.
		// But we'll do it all in a transaction so we can cancel it at the end.
		$this->storageHelper->beginTransaction();
		try {
			$this->testRester->doAction($act, $actx);
			return true;
		} catch( EarthIT_CMIPREST_ActionUnauthorized $e ) {
			foreach( $e->getNotes() as $n ) $notes[] = $n;
			return false;
		} finally {
			$this->storageHelper->endTransaction(false);
		}
	}
	
	protected function assertAllowedness( $expected, $userId, $meth, $path, $qs='', $contobj=null, $message=null ) {
		$act = $this->makeAction($meth,$path,$qs,$contobj);
		$actx = new PHPTemplateProjectNS_FakeActionContext($userId);
		$notes = array();
		$isAllowed = $this->isAllowed($act, $actx, $notes);
		/* $this->organizationPermissionChecker->preAuthorizeSimpleAction( $act, $actx, $notes );
		if( $isAllowed === EarthIT_CMIPREST_RESTActionAuthorizer::AUTHORIZED_IF_RESULTS_VISIBLE ) {
			$isAllowed = null;
			try {
				$this->testRester->doAction($act, $actx);
				// Aw naw we don't have any way to get at those notes!
				$isAllowed = true;
			} catch( EarthIT_CMIPREST_ActionUnauthorized $e ) {
				foreach( $e->getNotes() as $n ) $notes[] = $n;
				$isAllowed = false;
			}
		} */
		$this->assertEquals( $expected, $isAllowed,
			($message ? $message."\n" : "").
			var_export($isAllowed,true).' != '.var_export($expected,true)."\n".implode("\n", $notes)
		);
		/*
		echo "Yay, ".($userId === null ? "unauthenticated user" : $userId)." ".
						($isAllowed ? "may" : "mayn't")." $meth $path?$qs".
						($notes ? ":\n\t".implode("\n\t", $notes)."\n" : "\n");
		*/
	}
	
	protected function assertAllowed( $userId, $meth, $path, $qs='', $contobj=null, $message=null ) {
		$this->assertAllowedness( true, $userId, $meth, $path, $qs, $contobj, $message );
	}
	protected function assertUnallowed( $userId, $meth, $path, $qs='', $contobj=null, $message=null ) {
		$this->assertAllowedness( false, $userId, $meth, $path, $qs, $contobj, $message );
	}
	
	const ORGADMIN_USER_ID   = '1000048';
	const FACADMIN_USER_ID   = '1000049';
	const VISITOR_USER_ID    = '1000050';
	const UNATTACHED_USER_ID = '1000051';
	
	protected function assertCannotDoStuff($userId) {
		$this->assertUnallowed($userId, 'GET','/organizations');
		$this->assertUnallowed($userId, 'GET','/organizations/1000041');
	}
	
	public function testUninloggedUserCannotDoStuff() {
		$this->assertCannotDoStuff(null);
	}
	
	public function testUnattachedUserCannotDoStuff() {
		$this->assertCannotDoStuff(self::UNATTACHED_USER_ID);
	}
	
	public function testFacilityAdminCanSeeTheirOwnFacility() {
		$this->assertAllowed(self::FACADMIN_USER_ID, 'GET', '/aclTestFacilities/1000043');
	}
	
	public function testFacilityAdminCannotDoStuffToOtherFacilities() {
		$this->assertUnallowed(self::FACADMIN_USER_ID, 'GET', '/aclTestFacilities/1000044');
	}
	
	public function testOrgAdminOrgVisibility() {
		foreach( array(1000041) as $orgId ) {
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be allowed to see his own org");
		}
		foreach( array(1000042,1000043,1000044) as $orgId ) {
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be allowed to see orgs below his own");
		}
		foreach( array(1000052) as $orgId ) {
			// You should always be allowed to see organizations above you, too
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be allowed to see ancestor orgs of the one he's attached to");
		}
		foreach( array(1000053) as $orgId ) {
			// But not cousins of the one you're at
			$this->assertUnallowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should NOT be allowed to see cousin org to the one he's attached to");
		}
	}
	
	public function testFacilityAdminCanChangeOwnCurtains() {
		$this->assertAllowed(1000049, 'PATCH', "/aclTestFacilities/1000043", '', array('curtainColor'=>'blorange'),
			"Facility admin should be allowed to change his own facility's curtain color");
	}
	public function testFacilityAdminCannotChangeOthersCurtains() {
		foreach( array(1000042,1000044) as $facilityId ) {
			$this->assertUnallowed(1000049, 'PATCH', "/aclTestFacilities/$facilityId", '', array('curtainColor'=>'grurple'),
				"Facility admin should NOT be allowed to change others' facility's curtain colors");
		}
	}
	
	//// Chair stuff!
	
	public function testFacilityAdminCanSeeOwnChairs() {
		$this->assertAllowed(1000049, 'GET', "/aclTestChairs/1000054", '', null,
			"Facility admin should be allowed to read his own facility's chairs");
	}
	public function testFacilityAdminCanChangeOwnChairs() {
		$this->assertAllowed(1000049, 'PATCH', "/aclTestChairs/1000054", '', array('color'=>'breen'),
			"Facility admin should be allowed to change his own facility's chair's color");
	}
	public function testFacilityAdminCannotChangeOthersChairs() {
		$this->assertUnallowed(1000049, 'PATCH', "/aclTestChairs/1000055", '', array('color'=>'orilver'),
			"Facility admin should NOT be allowed to change others' facility's chair's colors");
	}
	
	//// POST that's actually a PATCH
	// If these pass we can probably assume that POSTS
	// are being translated to PATCHes as needed, so more thorough
	// tests can be done by regular PATCHing

	public function testFacilityAdminCanUpdateOwnChairsViaPost() {
		$this->assertAllowed(1000049, 'POST', "/aclTestChairs", '',
			array(array('id'=>1000054, 'facilityId'=>1000043, 'color'=>'broon')),
			"Facility should be allowed to update chairs at their own facility via a POST");
	}
	public function testFacilityAdminCanotUpdateOthersChairsViaPost() {
		$this->assertUnallowed(1000049, 'POST', "/aclTestChairs", '',
			array(array('id'=>1000055, 'facilityId'=>1000044, 'color'=>'broon')),
			"Facility should NOT be allowed to update chairs at others' facilities via a POST");
	}
	
	//// POST new items
	
	public function testFacilityAdminCanCreateNewChairsInOwnFacility() {
		$this->assertAllowed(1000049, 'POST', "/aclTestChairs", '',
			array(array('facilityId'=>1000043, 'color'=>'breen')),
			"Facility admin should be allowed to create chairs in his own facility");
	}
	public function testFacilityAdminCannotCreateNewChairsInOthersFacility() {
		$this->assertUnallowed(1000049, 'POST', "/aclTestChairs", '',
			array(array('facilityId'=>1000044, 'color'=>'breen')),
			"Facility admin should NOT be allowed to create chairs someone else's facility");
	}
	
	//// Delete items
	
	public function testFacilityAdminCanDeleteNonexistentChairs() {
		$this->assertAllowed(1000049, 'DELETE', "/aclTestChairs/1000057", '', null,
			"Facility admin should be allowed to delete nonexistent chairs");
	}
	public function testFacilityAdminCanDeleteOwnChairs() {
		$this->assertAllowed(1000049, 'DELETE', "/aclTestChairs/1000054", '', null,
			"Facility admin should be allowed to delete chair records for his own facility");
	}
	public function testFacilityAdminCannotDeleteForeignChairs() {
		$this->assertUnallowed(1000049, 'DELETE', "/aclTestChairs/1000055", '', null,
			"Facility admin should be allowed to delete foreign chairs");
	}
	
	//// Replace items using PUT
	
	public function testFacilityAdminCanReplaceNonexistentChairs() {
		$this->assertAllowed(1000049, 'PUT', "/aclTestChairs/1000057", '',
			array('facilityId' => 1000043, 'color' => 'invisible'),
			"Facility admin should be allowed to replace nonexistent chairs");
	}
	public function testFacilityAdminCanReplaceOwnChairs() {
		$this->assertAllowed(1000049, 'PUT', "/aclTestChairs/1000054", '',
			array('facilityId' => 1000043, 'color' => 'crapple'),
			"Facility admin should be allowed to replace chair records for his own facility");
	}
	public function testFacilityAdminCannotReplaceForeignChairs() {
		$this->assertUnallowed(1000049, 'PUT', "/aclTestChairs/1000055", '',
			array('facilityId' => 1000043, 'color' => 'smurple'),
			"Facility admin should be allowed to replace foreign chairs, even when changing the facility ID to their own");
		$this->assertUnallowed(1000049, 'PUT', "/aclTestChairs/1000055", '',
			array('facilityId' => 1000044, 'color' => 'smurple'),
			"Facility admin should be allowed to replace foreign chairs, even when not changing the facility ID");
	}
	public function testFacilityAdminCannotReplaceOwnFacility() {
		$this->assertUnallowed(1000049, 'PUT', "/aclTestFacilities/1000043", '',
			array('id' => 1000043, 'curtainColor' => 'stapple'),
			"Facility admin should NOT be allowed to replace their facility record");
	}
	
	//// Movement

	public function testOrgAdminCanMoveChairsBetweenOwnedFacilities() {
		$this->assertAllowed(self::ORGADMIN_USER_ID, 'PATCH', '/aclTestChairs/1000054', '',
			array('facilityId' => 1000042),
			"Org admin should be allowed to move brown chair betweem east and west facilities");
		$this->assertAllowed(self::ORGADMIN_USER_ID, 'PATCH', '/aclTestChairs/1000054', '',
			array('facilityId' => 1000044),
			"Org admin should be allowed to move brown chair betweem east facility to its garage");
	}
	public function testOrgAdminCannotMoveChairsToUnownedFacilities() {
		$this->assertUnallowed(self::ORGADMIN_USER_ID, 'PATCH', '/aclTestChairs/1000054', '',
			array('facilityId' => 1000053),
			"Org admin should NOT be allowed to move brown chair to cousin org");
	}
	public function testOrgAdminCannotMoveChairsFromUnownedFacilities() {
		$this->assertUnallowed(self::ORGADMIN_USER_ID, 'PATCH', '/aclTestChairs/1000058', '',
			array('facilityId' => 1000042),
			"Org admin should NOT be allowed to move orange chair from cousin facility");
	}
	
	/*
	 * Stuff left to test:
	 * Movement:
	 * - chair movement between owned facilities is not allowed for facility admins
	 * - facility movement between owned orgs is allowed for org admins
	 * - facility movement is not allowed for facility admins
	 */
}
