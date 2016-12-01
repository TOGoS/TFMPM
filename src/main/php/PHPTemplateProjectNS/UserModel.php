<?php

class PHPTemplateProjectNS_UserModel extends PHPTemplateProjectNS_Component
{
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
