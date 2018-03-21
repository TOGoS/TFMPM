<?php

class TFMPM_PageAction_SayHelloTo extends TFMPM_PageAction_TemplatePageAction
{
	protected $name;
	public function __construct(TFMPM_Registry $reg, $name) {
		parent::__construct($reg);
		$this->name = $name;
	}
	protected function getTemplateName() { return 'say-hi-to'; }
	protected function getTemplateParameters() { return ['name'=>$this->name]; }
}
