<?php

class PHPTemplateProjectNS_PasswordModel extends PHPTemplateProjectNS_Component
{
	public function hashPassword($password) {
		return password_hash($password, PASSWORD_BCRYPT, array(
			'cost' => 15,
		));
	}
	
	public function checkPassword($password, $passhash) {
		return password_verify($password, $passhash);
	}
}
