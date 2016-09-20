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
	
	protected function assertAllowedness( $expected, $userId, $meth, $path, $qs='', $contobj=null, $message=null ) {
		$act = $this->makeAction($meth,$path,$qs,$contobj);
		$actx = new PHPTemplateProjectNS_FakeActionContext($userId);
		$notes = array();
		$isAllowed = $this->organizationPermissionChecker->preAuthorizeSimpleAction( $act, $actx, $notes );
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
		}
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
		$this->assertAllowed(self::FACADMIN_USER_ID, 'GET', '/facilities/1000043');
	}
	
	public function testFacilityAdminCannotDoStuffToOtherFacilities() {
		$this->assertUnallowed(self::FACADMIN_USER_ID, 'GET', '/facilities/1000044');
	}
	
	public function testOrgAdminOrgVisibility() {
		foreach( array(1000041) as $orgId ) {
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be able to see his own org");
		}
		foreach( array(1000042,1000043,1000044) as $orgId ) {
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be able to see orgs below his own");
		}
		foreach( array(1000052) as $orgId ) {
			// You should always be able to see organizations above you, too
			$this->assertAllowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should be able to see ancestor orgs of the one he's attached to");
		}
		foreach( array(1000053) as $orgId ) {
			// But not cousins of the one you're at
			$this->assertUnallowed(self::ORGADMIN_USER_ID, 'GET', '/organizations/'.$orgId, '', null,
				"Org admin should NOT be able to see cousin org to the one he's attached to");
		}
	}
	
	public function testFacilityAdminCanChangeOwnCurtains() {
		$this->assertAllowed(1000049, 'PATCH', "/facilities/1000043", '', array('curtainColor'=>'blorange'),
			"Facility admin should be able to change his own facility's curtain color");
	}
	public function testFacilityAdminCannotChangeOthersCurtains() {
		foreach( array(1000042,1000044) as $facilityId ) {
			$this->assertUnallowed(1000049, 'PATCH', "/facilities/1000043", '', array('curtainColor'=>'grurple'),
				"Facility admin should NOT be able to change others' facility's curtain colors");
		}
	}
}
