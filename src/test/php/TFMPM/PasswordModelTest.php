<?php

class TFMPM_PasswordModelTest extends TFMPM_TestCase
{
	public function testHashPassword() {
		$pw = "Hello";
		$wrongPw = "Herro";
		$hashed = $this->passwordModel->hashPassword($pw);
		$this->assertTrue( $this->passwordModel->checkPassword($pw, $hashed),
			"checkPassword should return true for right password.");
		$this->assertFalse( $this->passwordModel->checkPassword($wrongPw, $hashed),
			"checkPassword should return false for wrong password.");
	}
}
