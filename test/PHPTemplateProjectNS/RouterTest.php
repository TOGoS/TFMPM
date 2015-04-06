<?php

class PHPTemplateProjectNS_RouterTest extends PHPTemplateProjectNS_TestCase
{
	public function testCompoundAction() {
		$ctx = new PHPTemplateProjectNS_RequestContext([
			'GET' => [],
			'POST' => [],
		], [
			'requestMethod' => 'POST',
			'pathInfo' => '/api;compound',
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
		$rez = $this->router->handleRequest($ctx);
		$this->assertEquals( 200, $rez->getStatusCode() );
		$rezCO = EarthIT_JSON::decode($rez->getContent());
		$this->assertTrue( is_array($rezCO) );
		
		$this->assertEquals(1001, $rezCO['getUser1001']['id']);
		$this->assertEquals(1003, $rezCO['getOrg1003']['id']);
	}
}
