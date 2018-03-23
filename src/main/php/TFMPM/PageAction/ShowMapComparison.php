<?php

class TFMPM_PageAction_ShowMapComparison extends TFMPM_PageAction_TemplatePageAction
{
	protected $mapFilters;
	public function __construct( $reg, array $mapFilters ) {
		parent::__construct($reg);
		$this->mapFilters = $mapFilters;
	}
	public function getTemplateName() { return 'map-comparison'; }
	public function getTemplateParameters() {
		$maps = $this->mapModel->getMaps($this->mapFilters);
		return array(
			'maps' => $maps,
		);
	}
}
