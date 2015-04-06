<?php

class PHPTemplateProjectNS_PageAction_SayHelloTo extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	protected $name;
	public function __construct(PHPTemplateProjectNS_Registry $reg, $name) {
		parent::__construct($reg);
		$this->name = $name;
	}
	protected function getTemplateName() { return 'say-hi-to'; }
	protected function getTemplateParameters() { return ['name'=>$this->name]; }
}
