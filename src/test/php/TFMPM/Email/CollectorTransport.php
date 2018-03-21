<?php

class TFMPM_Email_CollectorTransport implements Swift_Transport
{
	public $messages = array();
	
	public function isStarted() { return true; }
	public function start() { }
	public function stop() { }

	public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
		$this->messages[] = $message;
	}
	
	/**
	 * Register a plugin in the Transport.
	 *
	 * @param Swift_Events_EventListener $plugin
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin) {
		throw new Exception("Plugins not supported on ".get_class($this));
	}
}
