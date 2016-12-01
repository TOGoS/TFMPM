<?php

class PHPTemplateProjectNS_PasswordModel extends PHPTemplateProjectNS_Component
{
	public function hashPassword($password) {
		return hash_password($password, PASSWORD_BCRYPT, array(
			'cost' => 15,
		));
	}
	
	public function checkPassword($password, $passhash) {
		return password_verify($password, $passhash);
	}
}
