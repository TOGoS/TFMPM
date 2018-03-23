<?php

class TFMPM_Util
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
	
	public static function parseCoordinate($thing) {
		if( is_array($thing) ) {
			if(
				count($thing) == 2 and
				isset($thing[0]) and is_numeric($thing[0]) and
				isset($thing[1]) and is_numeric($thing[1])
			) {
				return array( (float)$thing[0], (float)$thing[1] );
			}
			else {
				throw new Exception("Invalid coordinate array: ".json_encode($thing));
			}
		} else if( is_string($thing) ) {
			$e = explode(',', $thing);
			return self::parseCoordinate($e);
		} else {
			throw new Exception("Don't know how to parse ".self::describe($thing)." as coordinate");
		}
	}
	
	public static function toSet($thing) {
		if( $thing === null ) return array();
		if( is_scalar($thing) ) return array($thing=>$thing);
		if( is_array($thing) ) {
			$set = array();
			foreach($thing as $v) $set[$v] = $v;
			return $set;
		}
		throw new Exception("Don't know how to settify ".TFMPM_Util::describe($thing));
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
