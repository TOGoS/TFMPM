<?php

class PHPTemplateProjectNS_Email_Util
{
	public static function parseEndpoint( $ep ) {
		if( is_array($ep) && isset($ep['address']) ) {
			$ep['name'] = coalesce($ep['name'], null);
			return $ep;
		}
		
		$ep = trim($ep);
		if( preg_match('/^[^<>@"\s]+@[^<>@"\s]+$/', $ep) ) {
			return array('address'=>$ep, 'name'=>null);
		} else if( preg_match('/^([^<]*)<\s*([^<>@"\s]+@[^<>@"\s]+)\s*>$/', $ep, $bif) ) {
			$name = trim($bif[1]);
			$addy = trim($bif[2]);
			if( preg_match('/^"(.*?)"$/', $name, $bif ) ) $name = $bif[1];
			if( strlen($name) == 0 ) $name = null;
			return array('address'=>$addy, 'name'=>$name);
		} else {
			throw new Exception("Malformed e-mail endpoint: $ep");
		}
	}
	
	public static function formatEndpoint( array $ep ) {
		if( isset($ep['name']) ) {
			return str_replace(array('"','<','>'),'',$ep['name']).' <'.$ep['address'].'>';
		} else {
			return $ep['address'];
		}
	}
	
	public static function endpointIsValid( $ep, &$reason ) {
		if( !is_array($ep) ) {
			$reason = "Endpoint is not an array: ".var_export($ep,true);
			return false;
		}
		return true;
	}
	
	public static function messageIsValid( $email, &$reason ) {
		if( !self::endpointIsValid( coalesce($email['sender']), $reason ) ) return false;
		foreach( $email['recipients'] as $rcpt ) {
			if( !self::endpointIsValid( coalesce($rcpt), $reason ) ) return false;
		}
		// TODO: Probably more checks we should do.
		return true;
	}
}
