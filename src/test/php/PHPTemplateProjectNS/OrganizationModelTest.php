<?php

/**
 * @group integration
 */
class PHPTemplateProjectNS_OrganizationModelTest extends PHPTemplateProjectNS_TestCase
{
	protected function assertOrgRlxn( $expectedRlxn, $orgAId, $orgBId ) {
		$rlxn = $this->organizationModel->getOrganizationRelationship( $orgAId, $orgBId );
		$this->assertEquals( $expectedRlxn, $rlxn );
	}
	
	public function testGetOrganization() {
		$org = $this->organizationModel->getOrganization('1000041');
		$this->assertNotNull($org);
		$this->assertEquals('1000041', $org['ID']);
		$this->assertEquals('ACL Test Org', $org['name']);
		$this->assertEquals('1000052', $org['parent ID']);
	}
	
	public function testThatOrgIsBelow() {
		$this->assertOrgRlxn( 'below', '1000042', '1000041' );
	}
	public function testThatOrgIsBelow2() {
		$this->assertOrgRlxn( 'below', '1000043', '1000041' );
	}
	public function testThatOrgIsABitFurtherBelow() {
		$this->assertOrgRlxn( 'below', '1000044', '1000041' );
	}

	public function testThatOrgIsAtItself() {
		$this->assertOrgRlxn( 'at', '1000042', '1000042' );
	}

	public function testThatOrgIsAbove() {
		$this->assertOrgRlxn( 'above', '1000041', '1000042' );
	}
	public function testThatOrgIsAbove2() {
		$this->assertOrgRlxn( 'above', '1000041', '1000043' );
	}
	public function testThatOrgIsABitFurtherAbove() {
		$this->assertOrgRlxn( 'above', '1000041', '1000044' );
	}

	public function testThatOrgIsUnrelated() {
		$this->assertOrgRlxn( 'unrelated-to', '1000042', '1000053' );
	}
}
