<?php

class TFMPM_PageAction_ShowComputations extends TFMPM_PageAction_TemplatePageAction
{
	public function getTemplateName() { return 'computations'; }
	public function getTemplateParameters() {
		$computations = $this->storageHelper->queryRows("SELECT * FROM tfmpm.computation");
		return array('computations' => $computations);
	}
}
