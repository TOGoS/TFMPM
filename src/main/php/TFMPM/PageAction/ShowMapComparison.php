<?php

class TFMPM_PageAction_ShowMapComparison extends TFMPM_PageAction_TemplatePageAction
{
	protected $mapFilters;
	public function __construct( $reg, array $mapFilters ) {
		parent::__construct($reg);
		$this->mapFilters = $mapFilters;
	}
	protected function getJsoSchema() {
		$schemaJso = array(
			'classes' => array()
		);
		foreach( $this->schema->getResourceClasses() as $rc ) {
			$rcJso = array(
				'fields' => array()
			);
			foreach( $rc->getFields() as $fn => $field ) {
				$fieldJso = array(
					'name' => $fn,
					'jsoName' => EarthIT_Schema_WordUtil::toCamelCase($fn),
					'valueTypeName' => $field->getType()->getName(),
					'includedInBasicInfo' => $field->getFirstPropertyValue('http://ns.nuke24.net/TFMPM/includedInBasicInfo', true),
				);
				$rcJso['fields'][$fn] = $fieldJso;
			}
			$schemaJso['classes'][$rc->getName()] = $rcJso;
		}
		return $schemaJso;
	}
	public function getTemplateName() { return 'map-comparison'; }
	public function getTemplateParameters() {
		$maps = $this->mapModel->getMaps($this->mapFilters, array('resourceStats'));
		$schema = $this->getJsoSchema();
		return array(
			'maps' => $maps,
			'schema' => $schema,
		);
	}
}
