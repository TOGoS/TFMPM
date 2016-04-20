<?php

/**
 * Functions to deal with different representations of SHA-1 hashes.
 */
class PHPTemplateProjectNS_BlobIDUtil
{
	public static function urnToBasename( $urn ) {
		if( preg_match( '/^urn:(?:sha1|bitprint):([0-9A-Z]{32})/', $urn, $bif ) ) {
			return $bif[1];
		}
		return null;
	}
	
	/**
	 * Return [URI, blob ID]
	 */
	public static function parseRef( $ref ) {
		if( preg_match( '/^urn:(?:sha1|bitprint):([0-9A-Z]{32})/', $ref, $bif ) ) {
			return [$ref, $bif[1]];
		} else if( preg_match('/^[0-9A-Z]{32}$/', $ref) ) {
			return ["urn:sha1:{$ref}", $ref];
		} else {
			throw new Exception("Ref not recognized as a URN or blob ID: '{$ref}'");
		}
	}
}
