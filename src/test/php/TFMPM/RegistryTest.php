<?php

class TFMPM_RegistryTest extends TFMPM_TestCase
{
	public function testRegistryRegistered() {
		global $TFMPM_Registry;
		$this->assertEquals( 'TFMPM_Registry', get_class($TFMPM_Registry) );
	}
}
