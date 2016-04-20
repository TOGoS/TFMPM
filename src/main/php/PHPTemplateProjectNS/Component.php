<?php

class PHPTemplateProjectNS_Component
{
	protected $registry;
	
	use PHPTemplateProjectNS_ComponentGears;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg ) {
		$this->registry = $reg;
	}
}
