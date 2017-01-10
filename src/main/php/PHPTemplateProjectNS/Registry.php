<?php

class PHPTemplateProjectNS_Registry
{
	protected $postResponseJobs = [];
	
	protected $projectRootDir;
	public function __construct( $projectRootDir ) {
		$this->projectRootDir = $projectRootDir;
	}
	
	protected function loadConfigFile($file) {
		$c = EarthIT_JSON::decode(file_get_contents($file), true);
		if( $c === null ) {
			throw new Exception("Failed to load config from '{$file}'");
		}
		return $c;
	}
	
	protected $configCache = [];
	public function getConfig( $name ) {
		$parts = explode('/', $name);
		$file = array_shift($parts);
		if( isset($this->configCache[$file]) ) {
			$c = $this->configCache[$file];
		} else {
			$cf = "{$this->projectRootDir}/config/{$file}.json";
			if( !file_exists($cf) ) return null;
			$c = $this->loadConfigFile($cf);
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
	
	/**
	 * Returns a single-element e-mail address => name array
	 * corresponding to the named entry in config/email-addresses.json,
	 * suitable for passing to $message->setFrom(...)
	 */
	public function getSwiftAddress( $key ) {
		$entry = $this->requireConfig("email-addresses/$key");
		return array( $entry['address'] => $entry['name'] );
	}
	
	public function requireConfig( $name ) {
		$v = $this->getConfig($name);
		if( $v === null ) throw new Exception("'$name' config variable not defined.");
		return $v;
	}
	
	/** Don't use this unless you're withConfig */
	public function setConfig( $name, $v ) {
		// Force it to get loaded:
		$this->getConfig($name);
		
		$parts = explode('/', $name);
		$lsat = array_pop($parts);
		$c =& $this->configCache;
		foreach( $parts as $p ) {
			$c =& $c[$p];
		}
		$c[$lsat] = $v;
	}
	
	public function loadDbAdapter() {
		return Doctrine_DBAL_DriverManager::getConnection( $this->getConfig('dbc') );
	}
	
	public function loadDbObjectNamer() {
		return new EarthIT_DBC_OverridableNamer(new EarthIT_DBC_PostgresNamer());
	}
	
	public function getRestNameFormatter() {
		return function($name, $plural=false) {
			if($plural) $name = EarthIT_Schema_WordUtil::pluralize($name);
			return EarthIT_Schema_WordUtil::toCamelCase($name);
		};
	}
	
	public function getRestSchemaObjectNamer() {
		return EarthIT_CMIPREST_Namers::getStandardCamelCaseNamer();		
	}
	
	public function loadSchema($name='') {
		return require $this->projectRootDir.'/target/schema/'.($name?$name.'.':'').'schema.php';
	}

	public function loadSqlRunner() {
		return new EarthIT_DBC_DoctrineSQLRunner($this->dbAdapter);
	}

	public function loadStorageHelper() {
		return new PHPTemplateProjectNS_NormalStorageHelper($this);
	}
	
	protected function loadSqlGenerator() {
		return new EarthIT_Storage_PostgresSQLGenerator($this->dbObjectNamer);
	}
	
	protected function loadSqlStorage() {
		return new EarthIT_CMIPREST_SQLStorage(
			$this->schema,
			$this->sqlRunner,
			$this->dbObjectNamer,
			$this->sqlGenerator);
	}

	protected function loadStorage() {
		return new PHPTemplateProjectNS_HJPKFixingStorage($this, $this->sqlStorage);
	}
	
	protected function loadRester() {
		return new EarthIT_CMIPREST_RESTer( array(
			'storage' => $this->storage,
			'schema' => $this->schema,
			'keyByIds' => true,
			'authorizer' => $this->restActionAuthorizer
		));
	}
	
	public function loadMailer() {
		$transportConfig = $this->getConfig('email-transport');
		
		$encryptionMethod = coalesce($transportConfig['encryption']); // 'SSL' and 'TLS' are supported

		$transport = Swift_SmtpTransport::newInstance($transportConfig['host'], coalesce($transportConfig['port'],25));
		$transport->setUsername($transportConfig['username']);
		$transport->setPassword($transportConfig['password']);
		if( $encryptionMethod) $transport->setEncryption(strtolower($encryptionMethod));
		
		if( $recipientOverride = coalesce($transportConfig['recipient-override']) ) {
			$transport = new PHPTemplateProjectNS_Email_RecipientOverrideTransport($transport, $recipientOverride);
		}
		
		return Swift_Mailer::newInstance($transport);
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
		$repoListFile = "{$this->projectRootDir}/config/local-ccouch-repos.lst";
		if( file_exists($repoListFile) ) {
			$repos = $this->readLstFile($repoListFile);
		} else {
			$repos = [];
		}
		array_unshift($repos, "{$this->projectRootDir}/datastore");
		return $repos;
	}
	
	protected function loadBlobRepository() {
		$repos = array();
		foreach( $this->getBlobRepositoryDirs() as $rd ) {
			$repos[] = new TOGoS_PHPN2R_FSSHA1Repository($rd);
		}
		$gitDir = "{$this->projectRootDir}/.git";
		if( is_dir($gitDir) ) {
			$repos[] = new TOGoS_PHPN2R_GitRepository($gitDir);
		}
		$multiRepo = new TOGoS_PHPN2R_MultiRepository($repos);
		$mappingFile = "{$this->projectRootDir}/.git-object-urns.txt";
		if( file_exists($mappingFile) ) {
			$multiRepo = new TOGoS_PHPN2R_URIMappingRepository($multiRepo, array(), array($mappingFile));
		}
		return $multiRepo;
	}
	
	protected function loadN2rServer() {
		return new TOGoS_PHPN2R_Server(array($this->blobRepository));
	}
	
	protected function loadPrimaryBlobRepository() {
		foreach( $this->getBlobRepositoryDirs() as $rd ) {
			return new TOGoS_PHPN2R_FSSHA1Repository($rd);
		}
		throw new Exception("No local repositories configured.");
	}
	
	protected function getViewTemplateDirectory() {
		return "{$this->projectRootDir}/src/views/php";
	}
	
	/**
	 * Components that have been explicitly configured.  Will not be
	 * wiped out by clean().
	 */
	protected $components = [];

	/**
	 * Components loaded lazily which will presumably be loaded the
	 * same way again if the the cache is cleared.  Will be emptied by
	 * clean().
	 */
	protected $cachedComponents = [];
	
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
	protected static $funnilyCasedComponentNames = ['ABC decoder', 'REST action authorizer'];
	
	public function __get($attrName) {
		// If something's been explicitly overridden, return that.
		if( isset($this->components[$attrName]) ) {
			return $this->components[$attrName];
		}
		
		// If there's a getter, call it and immediately return.
		$ucfAttrName = ucfirst($attrName);
		$getterMethodName = "get{$ucfAttrName}";
		if( method_exists($this, $getterMethodName) ) { 
			return $this->$getterMethodName();
		}

		// Check the cache.
		if( isset($this->cachedComponents[$attrName]) ) {
			return $this->cachedComponents[$attrName];
		}

		// If there's a loadX method, use it and cache the result.
		$creatorMethodName = "load{$ucfAttrName}";
		if( method_exists($this, $creatorMethodName) ) { 
			return $this->cachedComponents[$attrName] = $this->$creatorMethodName();
		}
		
		foreach( self::$funnilyCasedComponentNames as $n) {
			$n = trim($n);
			if( EarthIT_Schema_WordUtil::toCamelCase($n) == $attrName ) {
				// Ooh, this is what they want!
				$ucfAttrName = EarthIT_Schema_WordUtil::toPascalCase($n);
				break;
			}
		}
		
		// If there's a class with a matching name, instantiate it and cache the instance.
		$className = "PHPTemplateProjectNS_{$ucfAttrName}";
		if( class_exists($className,true) ) {
			return $this->cachedComponents[$attrName] = new $className($this);
		}
		
		throw new Exception("Undefined property: ".get_class($this)."#$attrName");
	}
	
	/**
	 * Use to explicitly override a component.
	 * 
	 * Don't use this directly.  Use with(...) instead to make a copy
	 * of the registry with the specified things replaced.a
	 */
	public function __set($attrName, $value) {
		$this->components[$attrName] = $value;
	}
	
	/**
	 * Don't use this directly, either.
	 * Use cleanClone() to get a copy of the registry with the cache cleared.
	 */
	protected function clean() {
		$this->cachedComponents = [];
	}

	/**
	 * Returns a copy of this Registry with the component cache cleared.
	 *
	 * This ensures that if any settings are changed on the clone that
	 * would affect how components are reloaded, their new values get
	 * used to load those components when they are requested.
	 */
	public function cleanClone() {
		$c = clone $this;
		$c->clean();
		return $c;
	}
	
	public function with(array $stuff) {
		$alt = $this->cleanClone();
		foreach( $stuff as $k=>$v ) $alt->$k = $v;
		return $alt;
	}
	
	public function withConfig($k, $v) {
		$alt = $this->cleanClone();
		$alt->setConfig($k, $v);
		return $alt;
	}
	
	public function withConfigFile($k, $filename) {
		return $this->withConfig($k, $this->loadConfigFile($filename));
	}
	
	public function withSchema(EarthIT_Schema $schema) {
		return $this->with(['schema'=>$schema]);
	}
	public function withNamedSchema($name) {
		return $this->withSchema($this->loadSchema($name));
	}
}
