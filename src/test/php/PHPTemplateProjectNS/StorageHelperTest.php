<?php

class PHPTemplateProjectNS_StorageHelperTest extends PHPTemplateProjectNS_TestCase
{
	public function testUpsertExistingUser() {
		$SH = $this->registry->storageHelper;
		
		$randoUsername = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$userId = $SH->newEntityId();
		
		$SH->insertNewItem('user', ['ID'=>$userId, 'username' => $randoUsername, 'passhash'=>'1234']);
		
		$SH->upsertItem('user', ['ID'=>$userId, 'username'=>$randoUsername.'x']);
		
		$fetchedUser = $SH->getItemById('user', $userId);
		$this->assertEquals([
			'ID'       => $userId,
			'username' => $randoUsername.'x',
			'passhash' => '1234',
			'e-mail address' => null
		], $fetchedUser);
	}
	
	public function testUpsertNewUser() {
		$SH = $this->registry->storageHelper;
		
		$randoUsername = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$userId = $SH->newEntityId();
		
		$SH->upsertItem('user', ['ID'=>$userId, 'username'=>$randoUsername]);
		
		$fetchedUser = $SH->getItemById('user', $userId);
		$this->assertEquals([
			'ID'       => $userId,
			'username' => $randoUsername,
			'passhash' => null,
			'e-mail address' => null
		], $fetchedUser);
	}
	
	public function testPostNewUser() {
		$SH = $this->registry->storageHelper;
		
		$randoUsername = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$userId = $SH->newEntityId();
		
		$postedUser = $SH->postItem('user', ['ID'=>$userId, 'username'=>$randoUsername]);
		$this->assertNotNull($postedUser['ID']);
		$this->assertEquals([
			'ID'       => $postedUser['ID'],
			'username' => $randoUsername,
			'passhash' => null,
			'e-mail address' => null
		], $postedUser);
	}
}
