<?php

class TFMPM_PasswordModel extends TFMPM_Component
{
	public function hashPassword($password) {
		return password_hash($password, PASSWORD_BCRYPT, array(
			'cost' => 10,
		));
	}
	
	public function checkPassword($password, $passhash) {
		return password_verify($password, $passhash);
	}
}
