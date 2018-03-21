<?php

/**
 * @group integration
 */
class TFMPM_StorageHelperTest extends TFMPM_TestCase
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
	
	protected function _testNestedTransaction($innerSuccess, $outerSuccess) {
		$userId = $this->storageHelper->newEntityId();
		$randoUsername0 = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$randoUsername1 = 'uest'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$randoUsername2 = 'vest'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		try {
			$user = $this->storageHelper->postItem('user', array(
				'ID' => $userId,
				'username' => $randoUsername0,
			));
			
			$this->storageHelper->beginTransaction();
			$this->storageHelper->upsertItem('user', array(
				'ID' => $userId,
				'username' => $randoUsername1,
			));
			$this->storageHelper->beginTransaction();
			$this->storageHelper->upsertItem('user', array(
				'ID' => $userId,
				'username' => $randoUsername2,
			));
			$this->storageHelper->endTransaction($innerSuccess);
			$this->storageHelper->endTransaction($outerSuccess);
			
			if( $outerSuccess ) {
				$expectedUsername = $innerSuccess ? $randoUsername2 : $randoUsername1;
			} else {
				$expectedUsername = $randoUsername0;
			}
			
			$fetched = $this->storageHelper->getItem('user', array('ID'=>$userId));
			$this->assertEquals( $expectedUsername, $fetched['username'] );
		} finally {
			$this->storageHelper->deleteItems('user', array('ID'=>$userId));
		}
	}
	
	public function testSuccessfulNestedTransaction() {
		$this->_testNestedTransaction( true,  true);
	}
	public function testFailingNestedTransaction() {
		$this->_testNestedTransaction(false,  true);
	}
	public function testFailingNestedTransactionInFailingOuterTransaction() {
		$this->_testNestedTransaction(false, false);
	}
	public function testSuccessfulNestedTransactionInFailingOuterTransaction() {
		$this->_testNestedTransaction( true, false);
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
	
	public function testFetchOrderWithUser() {
		$SH = $this->storageHelper;
		
		$userId = $SH->newEntityId();
		$orderId = $SH->newEntityId();
		$randoUsername = 'test'.rand(100000,999999).rand(100000,999999).rand(100000,999999);
		$address = $SH->postItem('postal address', [
			'street address' => '123 Floof Court',
			'unit address' => '#401',
			'city name' => 'Minneapolis',
			'region code' => 'MN',
			'postal code' => '55411',
			'country code' => 'USA',
		]);
		
		$user = [
			'ID' => $userId,
			'username' => $randoUsername,
			'passhash' => null,
			'e-mail address' => null,
		];
		$SH->postItem('user', $user);
		$SH->postItem('order', [
			'ID' => $orderId,
			'user ID' => $userId,
			'shipping address ID' => $address['ID'],
			'billing address ID' => $address['ID'],
		]);
		
		$order = $SH->getItem('order', ['ID'=>$orderId], [], ['user', 'shipping address', 'billing address']);
		$this->assertEquals([
			'ID' => $orderId,
			'user ID' => $userId,
			'shipping address ID' => $address['ID'],
			'billing address ID' => $address['ID'],
			'user' => $user,
			'shipping address' => $address,
			'billing address' => $address
		], $order);
	}
}
