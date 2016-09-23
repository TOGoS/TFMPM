<?php

class PHPTemplateProjectNS_Util
{
	public static function describe( $thing ) {
		if( $thing === null ) return 'null';
		if( is_object($thing) ) {
			return method_exists($thing,'__toString') ?
				$thing->__toString() :
				"a ".get_class($thing);
		}
		if( is_numeric($thing) ) return (string)$thing;
		if( is_boolean($thing) ) return $thing ? 'true' : 'false';
		return "a ".gettype($thing);
	}
	
	/**
	 * This is named to match PHP's built-in jsonSerialize method
	 * and doesn't actually 'serialize', but turns a value
	 * into a value that may be serialized using json_encode.
	 */
	public static function jsonSerializeRecursively( $v ) {
		if( $v === null ) return $v;
		if( is_scalar($v) ) return $v;
		if( is_object($v) ) {
			/* jsonSerialize defined by JsonSerializable in PHP >= 5.4 */
			if( method_exists($v,'jsonSerialize') ) {
				$v = $v->jsonSerialize();
			} else {
				$className = get_class($v);
				$v = get_object_vars($v);
				$v['phpClassName'] = $className;
			}
		}
		if( is_array($v) ) {
			$jsonableArray = [];
			foreach( $v as $k=>$subV ) {
				$jsonableArray[$k] = self::jsonSerializeRecursively($subV);
			}
			return $jsonableArray;
		}
		return "(".gettype($v).")";
	}
}
