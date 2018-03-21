<?php

class TFMPM_TokenModel extends TFMPM_Component
{
	protected function hashToken( $token ) {
		// While 'unsuitable for passwords',
		// hmac is probably fine for tokens,
		// since the tokens themselves are 160 bits of random data.
		return hash_hmac('sha1', $token, 'token-key', false);
	}
	
	public function getTokenActionIfValid( $token, array &$notes=[], $markUsed=false, $expectedActionScript=null ) {
		$tokenHash = $this->hashToken($token);
		$tokenAction = $this->storageHelper->getItemById('token action', $tokenHash);
		if( $tokenAction === null ) {
			$notes['not-found'] = "Token not found";
			return null;
		}
		if( !$tokenAction['re-useable'] and $tokenAction['usage time'] ) {
			$notes['used'] = "Token already used.";
			return null;
		}
		$now = time();
		$expiration = $tokenAction['expiration time'] == null ? null : strtotime( $tokenAction['expiration time'] );
		if( $expiration !== null and $now > $expiration ) {
			$notes['expired'] = "Token expired.";
			return null;
		}
		if( $expectedActionScript !== null and $tokenAction['action script'] !== $expectedActionScript ) {
			$notes['wrong-token-type'] = "Expected an '$expectedActionScript' token, but found something different.";
			return null;
		}
		if( $markUsed ) {
			$this->storageHelper->doQuery(
				"UPDATE tfmpm.tokenaction\n".
				"SET usagetime = CURRENT_TIMESTAMP\n".
				"WHERE tokenhash = {tokenHash}",
				['tokenHash' => $tokenHash]
			);
		}
		return $tokenAction;
	}
	
	public function newTokenAction( array $tokenAction ) {
		$randoStuff = [microtime()];
		for( $i=0; $i<10; ++$i ) {
			$randoStuff[] = openssl_random_pseudo_bytes(16);
		}
		$token = hash('sha1', implode('',$randoStuff), false);
		$tokenAction['token hash'] = $this->hashToken($token);
		$this->storageHelper->insertNewItem('token action', $tokenAction);
		$tokenAction['token'] = $token;
		return $tokenAction;
	}
	
	public function markUserTokensUsed( $halfUserId ) {
		$this->storageHelper->doQuery(
			"UPDATE tfmpm.tokenaction\n".
			"SET expirationtime = CURRENT_TIMESTAMP\n".
			"WHERE halfuserid = {halfUserId}",
			['halfUserId'=>$halfUserId]
		);
	}
}
