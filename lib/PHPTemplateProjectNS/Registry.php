<?php

class PHPTemplateProjectNS_Registry
{
	protected $configDir;
	public function __construct( $configDir ) {
		$this->configDir = $configDir;
	}
	
	protected $configCache = [];
	public function getConfig( $name ) {
		$parts = explode('/', $name);
		$file = array_shift($parts);
		if( isset($this->configCache[$file]) ) {
			$c = $this->configCache[$file];
		} else {
			$cf = "{$this->configDir}/{$file}.json";
			if( !file_exists($cf) ) return null;
			$c = json_decode(file_get_contents($cf), true);
			if( $c === null ) {
				throw new Exception("Failed to load config from '{$cf}'");
			}
			$this->configCache[$file] = $c;
		}
		foreach( $parts as $p ) {
			if( isset($c[$p]) ) {
				$c = $c[$p];
			} else {
				return null;
			}
		}
		return $c;
	}
	
	public function loadDbAdapter() {
		return Doctrine_DBAL_DriverManager::getConnection( $this->getConfig('dbc') );
	}

	public function loadDbNamer() {
		return new EarthIT_DBC_PostgresNamer();
	}
		
	public function loadSchema() {
		return require PHPTemplateProjectNS_ROOT_DIR.'/schema/schema.php';
	}

	public function loadSqlRunner() {
		return new EarthIT_DBC_DoctrineSQLRunner($this->dbAdapter);
	}
	
	protected function loadStorage() {
		return new EarthIT_CMIPREST_PostgresStorage(
			$this->dbAdapter, $this->schema, $this->dbNamer );
	}
	
	protected function loadRester() {
		return new EarthIT_CMIPREST_RESTer( array(
			'storage' => $this->storage,
			'schema' => $this->schema,
			'keyByIds' => true
		));
	}	
	
	/**
	 * Magic __get and __isset are slightly deficient due to inability
	 * to automatically glean whether abcXyz should be cased as AbcXyz,
	 * AbcXYZ, ABCXyz, or ABCXYZ.  e.g. abcDecoder would not get
	 * properly mapped to instantiating a
	 * PHPTemplateProjectNS_ABCDecoder because of casing.  In cases
	 * like those, define a loadAbcDecoder() method.
	 */
	protected $components = [];

	public function __isset($attrName) {
		try {
			return $this->$attrName !== null;
		} catch( Exception $e ) {
			throw $e;
			return false;
		}
	}
	
	public function __get($attrName) {
		$ucfAttrName = ucfirst($attrName);
		$getterMethodName = "get{$ucfAttrName}";
		if( method_exists($this, $getterMethodName) ) { 
			return $this->$getterMethodName();
		}
		
		if( isset($this->components[$attrName]) ) {
			return $this->components[$attrName];
		}
		
		$creatorMethodName = "load{$ucfAttrName}";
		if( method_exists($this, $creatorMethodName) ) { 
			return $this->components[$attrName] = $this->$creatorMethodName();
		}
		
		$className = "PHPTemplateProjectNS_{$ucfAttrName}";
		if( class_exists($className) ) {
			return $this->components[$attrName] = new $className($this);
		}
		
		throw new Exception("Undefined property: ".get_class($this)."#$attrName");
	}
}
