<?php

class PHPTemplateProjectNS_Email_RecipientOverrideTransportTest
extends PHPTemplateProjectNS_TestCase
{
	protected $cTransport;
	protected $roTransport;
	
	public function setUp() {
		$this->cTransport = new PHPTemplateProjectNS_Email_CollectorTransport();
		$this->roTransport = new PHPTemplateProjectNS_Email_RecipientOverrideTransport(
			$this->cTransport, 'jaque@jack.net'
		);
	}
	
	public function testPlainTextEmail() {
		$message = new Swift_Message();
		$message->setTo(array('test@test.com' => 'Mr. Test', 'best@test.com' => 'Ms. Best'));
		$message->setBody("I love salt!\n");
		$message->setContentType("text/plain; charset=utf-8");
		$this->roTransport->send($message);
		$this->assertEquals( 1, count($this->cTransport->messages) );
		foreach( $this->cTransport->messages as $m ) {
			foreach( $m->getTo() as $recipAddress=>$recipName ) {
				$this->assertEquals('jaque@jack.net', $recipAddress);
			}
			$this->assertEquals(
				"Original recipient: Mr. Test <test@test.com>, Ms. Best <best@test.com>\n".
				"\n".
				"I love salt!\n",
				$m->getBody()
			);
		}
	}
	
	public function testHtmlEmail() {
		$message = new Swift_Message();
		$message->setTo(array('test@test.com' => 'Mr. Test', 'best@test.com' => 'Ms. Best'));
		$message->setBody("<p>I love salt!</p>\n");
		$message->setContentType("text/html; charset=utf-8");
		$this->roTransport->send($message);
		$this->assertEquals( 1, count($this->cTransport->messages) );
		foreach( $this->cTransport->messages as $m ) {
			foreach( $m->getTo() as $recipAddress=>$recipName ) {
				$this->assertEquals('jaque@jack.net', $recipAddress);
			}
			$this->assertEquals(
				"<p>Original recipient: Mr. Test &lt;test@test.com&gt;, Ms. Best &lt;best@test.com&gt;</p>\n".
				"\n".
				"<p>I love salt!</p>\n",
				$m->getBody()
			);
		}
	}
}
