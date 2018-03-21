<?php

class TFMPM_DataTableResultAssemblerFactory
extends TFMPM_Component
implements EarthIT_CMIPREST_RequestParser_ResultAssemblerFactory
{
	public function getResultAssembler( $actionClass, array $options=array() ) {
		return $this->dataTableResultAssembler;
	}
}
