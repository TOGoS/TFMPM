<?php

class TFMPM_RequestTest
extends TFMPM_TestCase
{
	public function testParseFromJsonContent() {
		$getParams = array(
			'foo' => 'bar'
		);
		$content = EarthIT_JSON::encode(array(
			'hello' => 'world'
		));
		
		$req = new TFMPM_Request( array(
			'GET' => $getParams,
			'POST' => array(),
			'SERVER' => array(
				'REQUEST_METHOD' => 'POST',
				'CONTENT_TYPE' => 'application/json'
			)
		), array(
			'requestMethod' => 'POST',
			'pathInfo' => '/return-params',
			'requestContent' => $content,
		));
		
		// Loop a few times to make sure any caching isn't malfunctioning:
		for( $i=0; $i<3; ++$i ) {
			$this->assertEquals( array(
				'foo' => 'bar',
				'hello' => 'world',
			), $req->getParams() );
			
			$this->assertEquals( array(
				'hello' => 'world',
			), $req->getRequestContentObject() );
			
			$this->assertEquals('bar', $req->getParam('foo'));
			$this->assertEquals('world', $req->getParam('hello'));
		}
	}
}
