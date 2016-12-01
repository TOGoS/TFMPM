<?php

class PHPTemplateProjectNS_UserModel extends PHPTemplateProjectNS_Component
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
	
	/**
	 * @return array( 'success' => true|false, 'message'=>'Failed to log in because you are too tall', 'userId'=>123 )
	 */
	public function checkLogin( $username, $password ) {
		$users = $this->storageHelper->queryItems(
			'user',
			"SELECT *\n".
			"FROM phptemplateprojectdatabasenamespace.user\n".
			"WHERE username = {username} OR emailaddress = {username}\n",
			['username'=>$username]);
		
		// TODO: Check based on username or e-mail, then if there is ambiguity, just e-mail
		
		$possibleUsers = [];
		foreach( $users as $user ) {
			if( $this->passwordModel->checkPassword($password, $user['passhash']) ) {
				$possibleUsers[] = $user;
			}
		}

		switch( count($possibleUsers) ) {
		case 1:
			foreach( $possibleUsers as $user ) return array(
				'success' => true,
				'userId' => $user['ID']
			);
		case 0:
			return array(
				'success' => false,
				'message' => 'Invalid login credentials.'
			);
		default:
			return array(
				'success' => false,
				'message' => 'Ambiguous login credentials.'
			);
		}
	}
}
