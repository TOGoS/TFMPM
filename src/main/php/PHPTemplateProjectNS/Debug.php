<?php

class PHPTemplateProjectNS_Debug
{
	public static function describe($thing) {
		if( $thing === null ) return 'null';
		if( is_scalar($thing) ) return json_encode($thing);
		if( is_array($thing) ) return "an array";
		if( is_resource($thing) ) return "a resource";
		if( is_object($thing) ) return "an instance of ".get_class($thing);
		return "a ".gettype($thing);
	}
}
