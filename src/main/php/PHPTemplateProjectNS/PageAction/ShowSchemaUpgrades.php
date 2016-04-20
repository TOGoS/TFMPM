<?php

class PHPTemplateProjectNS_PageAction_ShowSchemaUpgrades extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	protected $mode;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, $mode='list' ) {
		parent::__construct($reg, null);
		$this->mode = $mode;
	}
	
	public function getTemplateName() { return 'schema-upgrades'; }
	public function getTemplateParameters() {
		$schemaUpgrades = $this->storageHelper->queryRows(
			"SELECT \"time\", scriptfilename AS \"script filename\", scriptfilehash AS \"script file hash\"\n".
			"FROM phptemplateprojectdatabasenamespace.schemaupgrade");
		$hashList = array();
		foreach( $schemaUpgrades as &$upgrade ) {
			$upgrade['script file URN'] = 'urn:sha1:'.TOGoS_Base32::encode(hex2bin($upgrade['script file hash']));
		}; unset($upgrade);
		if( $this->mode == 'full' ) {
			foreach( $schemaUpgrades as &$upgrade ) {
				$upgrade['script content'] = $this->blobRepository->getBlob($upgrade['script file URN']);
			}; unset($upgrade);
		}
		return array(
			'mode' => $this->mode,
			'schemaUpgrades' => $schemaUpgrades,
			'fingerprint' => hash('sha1', implode(',',$hashList), false),
		);
	}
}
