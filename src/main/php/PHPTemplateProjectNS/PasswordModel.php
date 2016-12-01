<?php

class PHPTemplateProjectNS_PasswordModel extends PHPTemplateProjectNS_Component
{
	public function hashPassword($password) {
		// TODO: Use that more recommended algorithm instead of hash_hmac.
		// http://php.net/manual/en/book.password.php
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
