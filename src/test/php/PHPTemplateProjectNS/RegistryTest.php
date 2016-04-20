<?php

class PHPTemplateProjectNS_RegistryTest extends PHPUnit_Framework_TestCase
{
	public function testRegistryRegistered() {
		global $PHPTemplateProjectNS_Registry;
		$this->assertEquals( 'PHPTemplateProjectNS_Registry', get_class($PHPTemplateProjectNS_Registry) );
	}
}
