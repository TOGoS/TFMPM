<?php

class PHPTemplateProjectNS_Registry extends EarthIT_Registry
{
	public function getDbAdapter() {
		if( $this->dbAdapter === null ) {
			$this->dbAdapter = Doctrine_DBAL_DriverManager::getConnection( $this->getConfig('dbc') );
		}
		return $this->dbAdapter;
	}

	public function getDbNamer() {
		return new EarthIT_DBC_PostgresNamer();
	}
		
	public function getRester() {
		return $this->getComponent('EarthIT_CMIPREST_RESTer');
	}
	
	public function getSchema() {
		return require PHPTemplateProjectNS_ROOT_DIR.'/schema/schema.php';
	}
}
