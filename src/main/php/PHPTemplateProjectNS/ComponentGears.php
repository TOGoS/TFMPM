<?php

/**
 * Mixin to add handy utility functions to component
 * and component-like classes.
 */
trait PHPTemplateProjectNS_ComponentGears
{
	/**
	 * Shortcut to get objects from the registry.  This has to be
	 * public because that's how __get works, but it's not intended to
	 * be used from outside.
	 */
	public function __get($attrName) {
		return $this->registry->$attrName;
	}
	
	protected function rc( $rc ) {
		if( is_string($rc) ) return $this->schema->getResourceClass($rc);
		if( $rc instanceof EarthIT_Schema_ResourceClass ) return $rc;
		throw new Exception("Invalid resource class or resource class name: ".var_export($rc,true));
	}
}
