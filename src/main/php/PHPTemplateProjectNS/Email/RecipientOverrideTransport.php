<?php

class PHPTemplateProjectNS_Email_RecipientOverrideTransport implements Swift_Transport
{
	protected $transport;
	protected $recipient;
	
	public function __construct( Swift_Transport $transport, $recipient ) {
		$this->transport = $transport;
		$this->recipient = PHPTemplateProjectNS_Email_Util::parseEndpoint($recipient);
	}
	
	public function isStarted() {
		return $this->transport->isStarted();
	}
	public function start() {
		$this->transport->start();
	}
	public function stop() {
		$this->transport->stop();
	}

	public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
		$orecipnames = array();
		foreach( $message->getTo() as $k => $v ) {
			$orecipnames[] = "$v <$k>";
		}
		
		$roHeader = "Original recipient: ".implode(', ', $orecipnames);
		if( preg_match('#^text/html(;|$)#', $message->getContentType()) ) {
			$roHeader = "<p>".htmlspecialchars($roHeader)."</p>";
		}
		$newBody = $roHeader."\n\n".$message->getBody();
		
		$newMessage = new Swift_Message();
		$newMessage->setTo( array($this->recipient['address'] => $this->recipient['name']) );
		$newMessage->setFrom( $message->getFrom() );
		$newMessage->setSubject( $message->getSubject() );
		$newMessage->setCharset( $message->getCharset() );
		$newMessage->setContentType( $message->getContentType() );
		$newMessage->setBody( $newBody );
		
		return $this->transport->send( $newMessage, $failedRecipients );
	}
	
	/**
	 * Register a plugin in the Transport.
	 *
	 * @param Swift_Events_EventListener $plugin
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin) {
		$this->transport->registerPlugin($plugin);
	}
}
