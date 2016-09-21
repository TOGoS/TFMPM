<?php

class PHPTemplateProjectNS_StorageHelperTest extends PHPTemplateProjectNS_TestCase
{
	public function testQueryValueMap() {
		$this->assertEquals(
			array(
				'foo' => 'bar',
				'baz' => 'quux',
			),
			$this->storageHelper->queryValueMap(
				"SELECT 'foo' AS k, 'x' AS v\n".
				"UNION ALL\n".
				"SELECT 'baz' AS k, 'quux' AS v\n".
				"UNION ALL\n".
				"SELECT 'foo' AS k, 'bar' AS v"
			)
		);
	}
	
	protected function _testNestedTransaction($success) {
		$userId = $this->storageHelper->newEntityId();
		$randoUsername = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$diffRandoUsername = 'best'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		try {
			$user = $this->storageHelper->postItem('user', array(
				'ID' => $userId,
				'username' => $randoUsername,
			));
			
			$this->storageHelper->beginTransaction();
			$this->storageHelper->beginTransaction();
			$this->storageHelper->upsertItem('user', array(
				'ID' => $userId,
				'username' => $diffRandoUsername,
			));
			$this->storageHelper->endTransaction($success);
			$this->storageHelper->endTransaction(true);
			
			$fetched = $this->storageHelper->getItem('user', array('ID'=>$userId));
			$this->assertEquals( $success ? $diffRandoUsername : $randoUsername, $fetched['username'] );
		} finally {
			$this->storageHelper->deleteItems('user', array('ID'=>$userId));
		}
	}
	
	public function testSuccessfulNestedTransaction() {
		$this->_testNestedTransaction(true);
	}
	public function testFailingNestedTransaction() {
		$this->_testNestedTransaction(false);
	}
	
	public function testUpsertExistingUser() {
		$SH = $this->storageHelper;
		
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
		$SH = $this->storageHelper;
		
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
		$SH = $this->storageHelper;
		
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
