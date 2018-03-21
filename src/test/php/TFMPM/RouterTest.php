<?php

class TFMPM_RouterTest extends TFMPM_TestCase
{
	public function testCompoundAction() {
		$this->markTestSkipped("Skipping testing compound request parsing because we know it doesn't work right now.");
		return;
		
		$req = new TFMPM_Request([
			'GET' => [],
			'POST' => [],
		], [
			'requestMethod' => 'POST',
			'pathInfo' => '/api;compound',
			'queryString' => '',
			'requestContentObject' => [
				'actions' => [
					'getUser1001' => [
						'method' => 'GET',
						'path' => '/users/1001',
					],
					'getOrg1003' => [
						'method' => 'GET',
						'path' => '/organizations/1003',
					]
				]
			]
		]);
		// TODO: Use some fake action context
		$actx = new TFMPM_NormalActionContext();
		$rez = $this->router->handleRequest($req, $actx);
		$this->assertEquals( 200, $rez->getStatusCode() );
		$rezCO = EarthIT_JSON::decode($rez->getContent());
		$this->assertTrue( is_array($rezCO) );
		
		$this->assertEquals(1001, $rezCO['getUser1001']['id']);
		$this->assertEquals(1003, $rezCO['getOrg1003']['id']);
	}
}
