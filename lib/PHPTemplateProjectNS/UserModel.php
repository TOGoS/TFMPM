<?php

class PHPTemplateProjectNS_UserModel extends PHPTemplateProjectNS_Component
{
	public function hashPassword($password) {
		$salt = mt_rand()."-".mt_rand();
		return $salt.':'.hash_hmac('sha1', $password, $salt);
	}
	
	public function checkPassword($password, $passhash) {
		$parts = explode(':',$passhash,2);
		if( count($parts) != 2 ) {
			return false;
		}
		list($salt,$hash) = $parts;
		return hash_hmac('sha1', $password, $salt) === $hash;
	}
}
