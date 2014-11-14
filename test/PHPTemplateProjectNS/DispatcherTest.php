<?php

class PHPTemplateProjectNS_DispatcherTest extends PHPUnit_Framework_TestCase
{
	protected $dispatcher;
	public function setUp() {
		global $PHPTemplateProjectNS_Registry;
		$this->dispatcher = $PHPTemplateProjectNS_Registry->getComponent('PHPTemplateProjectNS_Dispatcher');
	}
	
	public function testCompoundAction() {
		$rez = $this->dispatcher->handleRequest( 'POST', '/api;compound', array(), array(
			'actions' => array(
				'getUser1001' => array(
					'method' => 'GET',
					'path' => '/users/1001',
				),
				'getOrg1003' => array(
					'method' => 'GET',
					'path' => '/organizations/1003',
				)
			)
		));
		$this->assertEquals( 200, $rez->getStatusCode() );
		$rezCO = json_decode((string)$rez->getContent(), true);
		$this->assertTrue( is_array($rezCO) );
		
		$this->assertEquals(1001, $rezCO['getUser1001']['id']);
		$this->assertEquals(1003, $rezCO['getOrg1003']['id']);
	}
}
