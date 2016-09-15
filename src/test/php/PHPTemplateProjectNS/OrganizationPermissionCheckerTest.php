<?php

class PHPTemplateProjectNS_OrganizationPermissionCheckerTest extends PHPTemplateProjectNS_TestCase
{
	// For these tests wee'll assume things are set up as by
	// build/db/test-data/1003-acl-test-data.sql
	
	protected function makeAction( $meth, $path, $qs='', $contobj=null ) {
		$contentBlob = $contobj ? EarthIT_JSON_PrettyPrintedJSONBlob($contobj) : null;
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
	
	protected function assertAllowedness( $expected, $userId, $meth, $path, $qs='', $contobj=null ) {
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
		$this->assertEquals( $expected, $isAllowed, var_export($isAllowed,true).' != '.var_export($expected,true)."\n".implode("\n", $notes) );
		/*
		echo "Yay, ".($userId === null ? "unauthenticated user" : $userId)." ".
						($isAllowed ? "may" : "mayn't")." $meth $path?$qs".
						($notes ? ":\n\t".implode("\n\t", $notes)."\n" : "\n");
		*/
	}
	
	protected function assertAllowed( $userId, $meth, $path, $qs='', $contobj=null ) {
		$this->assertAllowedness( true, $userId, $meth, $path, $qs, $contobj );
	}
	protected function assertUnallowed( $userId, $meth, $path, $qs='', $contobj=null ) {
		$this->assertAllowedness( false, $userId, $meth, $path, $qs, $contobj );
	}
	
	const ORGADMIN_USER_ID   = '1000048';
	const FACADMIN_USER_ID   = '1000049';
	const VISITOR_USER_ID    = '1000050';
	const UNATTACHED_USER_ID = '1000051';
	
	protected function assertCantDoStuff($userId) {
		$this->assertUnallowed($userId, 'GET','/organizations');
		$this->assertUnallowed($userId, 'GET','/organizations/1000041');
	}
	
	public function testUninloggedUserCantDoStuff() {
		$this->assertCantDoStuff(null);
	}
	
	public function testUnattachedUserCantDoStuff() {
		$this->assertCantDoStuff(self::UNATTACHED_USER_ID);
	}
	
	public function testFacilityAdminCanSeeTheirOwnFacility() {
		$this->assertAllowed(self::FACADMIN_USER_ID, 'GET', '/facilities/1000043');
	}
	
	public function testFacilityAdminCantDoStuffToOtherFacilities() {
		$this->assertUnallowed(self::FACADMIN_USER_ID, 'GET', '/facilities/1000044');
	}
}
