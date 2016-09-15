<?php

class PHPTemplateProjectNS_OrganizationPermissionCheckerTest extends PHPTemplateProjectNS_TestCase
{
	// For these tests wee'll assume things are set up as by
	// build/db/test-data/1003-acl-test-data.sql
	
	protected function makeAction( $meth, $path, $qs='', $contobj=null ) {
		$contentBlob = $contobj ? EarthIT_JSON_PrettyPrintedJSONBlob($contobj) : null;
		return $this->router->apiRequestToAction($meth, $path, $qs, $contentBlob);
	}
	
	protected function assertAllowedness( $expected, $userId, $meth, $path, $qs='', $contobj=null ) {
		$act = $this->makeAction($meth,$path,$qs,$contobj);
		$actx = new PHPTemplateProjectNS_FakeActionContext($userId);
		$notes = array();
		$isAllowed = $this->organizationPermissionChecker->isActionAllowed( $act, $actx, $notes );
		$this->assertEquals( false, $isAllowed, var_export($isAllowed,true).' != '.var_export($expected,true)."\n".implode("\n", $notes) );
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
}
