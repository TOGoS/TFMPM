<?php

class TFMPM_PageAction_FileUploadTest extends TFMPM_TestCase
{
	protected $tempFiles = array();
	
	protected function newRandomFile() {
		$text = "";
		for( $i=rand(10,9999); $i>=0; --$i ) {
			$text .= chr(rand(0,255));
		}
		$base32Sha1 = TOGoS_Base32::encode(hash('sha1', $text, true));
		$tempDir = sys_get_temp_dir();
		$tempFile = "$tempDir/$base32Sha1";
		file_put_contents($tempFile, $text);
		return array(
			'tempFile' => $tempFile,
			'base32Sha1' => $base32Sha1,
		);
	}
	
	protected function _testUpload($testOptions, $constructorOptions, array $requestSuperglobals=array()) {
		$f = $this->newRandomFile();

		$requestSuperglobals['SERVER']['CONTENT_TYPE'] = 'multipart/form-data';
		$requestSuperglobals['FILES'][0] = array('name'=>'test.txt', 'tmp_name' => $f['tempFile']);
		$req = new TFMPM_Request($requestSuperglobals);

		$act = new TFMPM_PageAction_FileUpload($this->registry, $req, $constructorOptions);
		$actx = new TFMPM_FakeActionContext();
		$res = call_user_func($act, $actx);
		
		$expectedSector = $testOptions['sector'];
		$base32Sha1First2 = substr($f['base32Sha1'],0,2);
		$expectedWrittenFile = "{$this->registry->projectRootDir}/datastore/data/{$expectedSector}/{$base32Sha1First2}/{$f['base32Sha1']}";
		$this->assertTrue( file_exists($expectedWrittenFile), "Expected $expectedWrittenFile to have been written!");

		$storedBase32Sha1 = TOGoS_Base32::encode(hash_file('sha1', $expectedWrittenFile, true));
		$this->assertEquals($f['base32Sha1'], $storedBase32Sha1);
	}
	
	public function testUploadToDefaultSector() {
		$this->_testUpload(array('sector'=>'blah'), array('sector'=>'blah'));
	}
	
	public function testNonOverridableSector() {
		$this->_testUpload(
			array('sector'=>'blah'),
			array('sector'=>'blah'),
			array('SERVER'=>array('HTTP_X_CCOUCH_SECTOR'=>'overrode')));
	}
	
	public function testOverridableSector() {
		$this->_testUpload(
			array('sector'=>'overrode'),
			array('sector'=>'blah', 'allowSectorOverride' => true),
			array('SERVER'=>array('HTTP_X_CCOUCH_SECTOR'=>'overrode')));
	}

	public function tearDown() {
		foreach( $this->tempFiles as $f ) unlink($f);
		$this->tempFiles = array();
	}
}
