<?php

class TFMPM_Component
{
	protected $registry;
	
	use TFMPM_ComponentGears;
	
	public function __construct( TFMPM_Registry $reg ) {
		$this->registry = $reg;
	}
}
