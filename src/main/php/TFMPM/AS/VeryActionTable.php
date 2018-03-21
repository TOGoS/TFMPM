<?php

class TFMPM_AS_VeryActionTable extends TFMPM_Component implements ArrayAccess
{
	public function offsetGet($k) {
		$words = explode('-', $k);
		$className = "TFMPM_AS_".EarthIT_Schema_WordUtil::toPascalCase($words);
		if( class_exists($className) ) {
			return new $className( $this->registry );
		}
		return null;
	}
	
	public function offsetExists($k) {
		return $this->offsetGet($k) !== null;
	}
	
	public function offsetSet($k,$v) { throw new Exception("Naw"); }
	
	public function offsetUnset($k) {
		throw new Exception("Can't unset my stuff");
	}
}
