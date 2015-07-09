<?php

class PHPTemplateProjectNS_RouterTest extends PHPTemplateProjectNS_TestCase
{
	public function testCompoundAction() {
		$req = new PHPTemplateProjectNS_Request([
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
		$actx = new PHPTemplateProjectNS_NormalActionContext();
		$rez = $this->router->handleRequest($req, $actx);
		$this->assertEquals( 200, $rez->getStatusCode() );
		$rezCO = EarthIT_JSON::decode($rez->getContent());
		$this->assertTrue( is_array($rezCO) );
		
		$this->assertEquals(1001, $rezCO['getUser1001']['id']);
		$this->assertEquals(1003, $rezCO['getOrg1003']['id']);
	}
}
