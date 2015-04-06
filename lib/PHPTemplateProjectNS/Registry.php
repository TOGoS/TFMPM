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

	public function loadStorageHelper() {
		return new PHPTemplateProjectNS_NormalStorageHelper(
			$this->sqlRunner, $this->schema, $this->dbNamer);
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
	
	protected function readLstFile( $f ) {
		$data = file_get_contents($f);
		$rez = array();
		foreach( explode("\n",$data) as $l ) {
			$l = trim($l);
			if( $l == '' ) continue;
			if( $l[0] == '#' ) continue;
			$rez[] = $l;
		}
		return $rez;
	}
	
	protected function getBlobRepositoryDirs() {
		$repoListFile = "{$this->configDir}/local-ccouch-repos.lst";
		if( file_exists($repoListFile) ) {
			$repos = $this->readLstFile($repoListFile);
		} else {
			$repos = [];
		}
		$repos[] = PHPTemplateProjectNS_ROOT_DIR.'/blobstore';
		return $repos;
	}
	
	protected function loadN2rServer() {
		$repos = array();
		foreach( $this->getBlobRepositoryDirs() as $rd ) {
			$repos[] = new TOGoS_PHPN2R_FSSHA1Repository($rd);
		}
		return new TOGoS_PHPN2R_Server($repos);
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
	
	/*
	 * List names of any component classes that where the casing of their ClassName
	 * differes from that of their attributeName by more than the first letter.
	 * e.g. classes whose names contain acronyms.
	 *
	 * If you've defined a loadXyz function, then this is unnecessary.
	 */
	protected static $funnilyCasedComponentNames = ['ABC decoder'];
	
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
		foreach( self::$funnilyCasedComponentNames as $fccn ) {
			if( EarthIT_Schema_WordUtil::toCamelCase($fccn) == $attrName ) {
				$className = "PHPTemplateProjectNS_".EarthIT_Schema_WordUtil::toPascalCase($fccn);
			}
		}
		
		if( class_exists($className) ) {
			return $this->components[$attrName] = new $className($this);
		}
		
		throw new Exception("Undefined property: ".get_class($this)."#$attrName");
	}
}
