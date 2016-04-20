<?php

class PHPTemplateProjectNS_UserModelTest extends PHPTemplateProjectNS_TestCase
{
	public function testGeneratePassword() {
		$pw1 = $this->userModel->generatePassword(19);
		$pw2 = $this->userModel->generatePassword(19);
		$this->assertEquals(19, strlen($pw1));
		$this->assertNotEquals($pw1, $pw2);
	}
}
