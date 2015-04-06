<?php

abstract class PHPTemplateProjectNS_PageAction_TemplatePageAction extends PHPTemplateProjectNS_PageAction
{
	protected function getStatusCode() { return 200; }
	protected function getHeaders() { return ['content-type'=>'text/html; charset=utf-8']; }
	protected abstract function getTemplateName();
	protected abstract function getTemplateParameters();
	
	public function __invoke() {
		return $this->makeTemplateResponse(
			$this->getStatusCode(),
			$this->getTemplateName(),
			$this->getTemplateParameters(),
			$this->getHeaders()
		);
	}
}
