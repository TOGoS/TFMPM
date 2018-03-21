<?php

class TFMPM_UserModelTest extends TFMPM_TestCase
{
	const PASSWORD_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	public function generatePassword( $length=20 ) {
		$rm = strlen(self::PASSWORD_CHARS)-1;
		$pw = '';
		for( $i=0; $i<$length; ++$i ) {
			$idx = mt_rand(0, $rm);
			$pw .= substr(self::PASSWORD_CHARS, $idx, 1);
		}
		return $pw;
	}
	
	public function testGeneratePassword() {
		$pw1 = $this->generatePassword(19);
		$pw2 = $this->generatePassword(19);
		$this->assertEquals(19, strlen($pw1));
		$this->assertNotEquals($pw1, $pw2);
	}
}
