<?php

/** @group integration */
class TFMPM_PostalAddressTest extends TFMPM_TestCase
{
	protected static function withoutKey($k, $arr) {
		unset($arr[$k]);
		return $arr;
	}
	
	public function testPostAddress() {
		$addr1 = array(
			'street address' => '123 Scoop Street',
			'unit address' => '#302',
			'city name' => 'Boopville',
			'region code' => 'WY',
			'postal code' => '82941',
			'country code' => 'USA',
		);
		$addr2 = array(
			'street address' => '123 Boop Street',
			'unit address' => '#302',
			'city name' => 'Scoopville',
			'region code' => 'WY',
			'postal code' => '82941',
			'country code' => 'USA',
		);

		$savedAddr1      = $this->storageHelper->postItem('postal address', $addr1);
		$savedAgainAddr1 = $this->storageHelper->postItem('postal address', $addr1);
		$savedAddr2      = $this->storageHelper->postItem('postal address', $addr2);

		$this->assertEquals( $savedAddr1['ID'], $savedAgainAddr1['ID'] );
		$this->assertNotEquals( $savedAddr1['ID'], $savedAddr2['ID'] );
		$this->assertTrue( (bool)preg_match('/^[A-Z2-7]{32}$/', $savedAddr1['ID']) );
		$this->assertTrue( (bool)preg_match('/^[A-Z2-7]{32}$/', $savedAddr2['ID']) );

		$gotAddr2 = $this->storageHelper->getItemById('postal address', $savedAddr2['ID']);
		$this->assertEquals( $addr2, self::withoutKey('ID', $gotAddr2) );
	}
}
