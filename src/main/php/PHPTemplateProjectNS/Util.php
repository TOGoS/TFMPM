<?php

class PHPTemplateProjectNS_Util
{
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
