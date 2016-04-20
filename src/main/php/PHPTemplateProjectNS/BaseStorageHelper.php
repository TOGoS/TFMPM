<?php

abstract class PHPTemplateProjectNS_BaseStorageHelper implements PHPTemplateProjectNS_StorageHelper
{
	protected function rc( $rc ) {
		if( is_string($rc) ) return $this->schema->getResourceClass($rc);
		if( $rc instanceof EarthIT_Schema_ResourceClass ) return $rc;
		throw new Exception("Invalid resource class or resource class name: ".var_export($rc,true));
	}
	
	
}
