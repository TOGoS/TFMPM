<?php

class PHPTemplateProjectNS_NormalActionContextTest extends PHPTemplateProjectNS_TestCase
{
	protected function _testRelativeUrl( $rel, $from, $to ) {
		$actx = new PHPTemplateProjectNS_NormalActionContext();
		$actx = $actx->with(array('pathInfo' => $from));
		$this->assertEquals( $rel, $actx->relativeUrl($to) );
	}
	
	public function testRelativeUrl() {
		$this->_testRelativeUrl('../../fence/chicken', '/barn/cow/udder', '/fence/chicken');
		$this->_testRelativeUrl('../fence/chicken', '/barn/cow', '/fence/chicken');
		$this->_testRelativeUrl('fence/chicken', '/', '/fence/chicken');
		$this->_testRelativeUrl('./', '/fence', './');
	}
}
