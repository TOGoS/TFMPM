<?php

use TFMPM_FormBlob as FB;

class TFMPM_FormBlobTest extends TFMPM_TestCase
{
	public function testFormBlob() {
		$formInfo = EarthIT_JSON::decode(file_get_contents(__DIR__.'/test-form-info.json'));
		$formBlob = new FB($formInfo, array(
			FB::INCLUDE_FORM_ELEMENT => true,
			FB::FORM_ID     => 'le-test',
			FB::FORM_METHOD => 'POST',
			FB::FORM_ACTION => 'place'
		));
		$expectedHtml = file_get_contents(__DIR__.'/test-form-expected.html');
		$actualHtml = Nife_Util::stringifyBlob($formBlob)."\n";
		if( $actualHtml != $expectedHtml ) {
			file_put_contents('test-form-actual.html', $actualHtml);
		}
		$this->assertEquals($expectedHtml, $actualHtml);
	}
}
