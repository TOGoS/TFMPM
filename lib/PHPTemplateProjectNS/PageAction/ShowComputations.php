<?php

class PHPTemplateProjectNS_PageAction_ShowComputations extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	public function getTemplateName() { return 'computations'; }
	public function getTemplateParameters() {
		$computations = $this->storageHelper->queryRows("SELECT * FROM phptemplateprojectdatabasenamespace.computation");
		return array('computations' => $computations);
	}
}
