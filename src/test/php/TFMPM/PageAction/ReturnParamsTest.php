<?php

class TFMPM_PageAction_ReturnParamsTest
extends TFMPM_TestCase
{
	public function testParseFromJsonContent() {
		$object = array(
			'hello' => 'world'
		);

		$req = new TFMPM_Request( array(
			'GET' => array('foo'=>'bar'),
			'POST' => array(),
			'SERVER' => array(
				'REQUEST_METHOD' => 'POST',
				'CONTENT_TYPE' => 'application/json'
			)
		), array(
			'requestMethod' => 'POST',
			'pathInfo' => '/return-params',
			'requestContent' => EarthIT_JSON::encode($object),
		));
		$act = $this->router->requestToAction( $req );
		$actx = new TFMPM_FakeActionContext();
		$res = call_user_func($act, $actx);
		$this->assertEquals(200, $res->getStatusCode());
		$this->assertEquals(
			"{\n".
			"\t\"foo\": \"bar\",\n".
			"\t\"hello\": \"world\"\n".
			"}",
			(string)$res->getContent()
		);
	}
}
