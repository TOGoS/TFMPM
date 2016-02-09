<?php

class PHPTemplateProjectNS_DataTableResultAssemblerFactory
extends PHPTemplateProjectNS_Component
implements EarthIT_CMIPREST_RequestParser_ResultAssemblerFactory
{
	public function getResultAssembler( $actionClass ) {
		return $this->dataTableResultAssembler;
	}
}
