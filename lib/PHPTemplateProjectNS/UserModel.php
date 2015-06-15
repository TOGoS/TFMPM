<?php

class PHPTemplateProjectNS_UserModel extends PHPTemplateProjectNS_Component
{
	public function hashPassword($password) {
		$salt = mt_rand()."-".mt_rand();
		return $salt.':'.hash_hmac('sha1', $password, $salt);
	}
	
	public function checkPassword($password, $passhash) {
		list($salt,$hash) = explode(':',$passhash,2);
		return hash_hmac('sha1', $password, $salt) === $hash;
	}
}
