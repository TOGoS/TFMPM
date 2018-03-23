<?php

class TFMPM_PageAction_ShowHello extends TFMPM_PageAction_TemplatePageAction
{
	protected $mapFilters;
	public function __construct( $reg, array $mapFilters ) {
		parent::__construct($reg);
		$this->mapFilters = $mapFilters;
	}
	public function getTemplateName() { return 'hello'; }
	public function getTemplateParameters() {
		$mapDatabaseSummary = $this->mapModel->getDatabaseSummary();
		$mapFilterMetadata = $this->mapModel->getMapFilterMetadata($this->mapFilters);
		foreach( $mapFilterMetadata as $fieldCode => &$filter ) {
			if( isset($filter['values']) ) {
				$filter['selectedValues'] = isset($this->mapFilters[$fieldCode]) ?	$this->mapFilters[$fieldCode] : array();
			}
		}
		return array(
			'mapDatabaseSummary' => $mapDatabaseSummary,
			'mapFilterMetadata' => $mapFilterMetadata,
		);
	}
}
