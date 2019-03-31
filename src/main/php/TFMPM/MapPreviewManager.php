<?php

class TFMPM_MapPreviewManager extends TFMPM_Component
{
	protected static $mapGenerationParameterMetadata = array(
		'map seed' => array(
			'aliases'=> array('map gen seed')
		),
		'factorio commit ID' => array(),
		'map gen settings' => array(
			'description' => 'Custom map gen settings JSON as a string',
			'requiresValue' => false
		),
		'map gen settings file' => array(
			'description' => 'Path to file containing custom map gen settings',
			'requiresValue' => false
		),
		'data commit ID' => array(
			'requiresValue' => false
		),
		'map scale' => array(
			'aliases' => array('map preview scale'),
			'defaultValue' => 1,
		),
		'slope shading' => array(
			'defaultValue' => '0.3'
		),
		'map width' => array(
			'defaultValue' => 1024
		),
		'map offset' => array(
			'defaultValue' => '0,0'
		),
		'report quantities' => array(
			'description' => 'Comma-separated list of names of prototype to report placement statistics about',
			'defaultValue' => '',
		),
	);

	public static function getMapGenerationParameterMetadata() {
		return self::$mapGenerationParameterMetadata;
	}

	public function parseMapGenerationParamsFromCommandLine($argv, array &$runOptions=array() ) {
		$paramMetadata = self::getMapGenerationParameterMetadata();
		
		$paramReMap = array();
		foreach( $paramMetadata as $k=>&$info ) {
			$info['name'] = EarthIT_Schema_WordUtil::toCamelCase($k);
			$argRoot = "--".EarthIT_Schema_WordUtil::toKebabCase($k);
			$info['argRoot'] = $argRoot;
			$re = "/^$argRoot=(.*)$/";
			$paramReMap[$re] = $info;
		}; unset($info);

		$params = array();
		for( $i=1; $i<count($argv); ++$i ) {
			$arg = $argv[$i];
			foreach( $paramReMap as $regex=>$paramInfo ) {
				if( preg_match($regex, $arg, $bif) ) {
					$params[$paramInfo['name']] = $bif[1];
					continue 2;
				}
			}
			if( $arg == '-v' ) {
				$runOptions['verbosity'] = 100;
			} else if( $arg == '-vv' ) {
				$runOptions['verbosity'] = 200;
			} else if( $arg == '--help' ) {
				echo "Options:\n";
				echo "  --help ; show this\n";
				echo "  --lazy ; skip generating maps already generated (according to the SQLite database)\n";
				foreach( $paramMetadata as $param ) {
					echo "  {$param['argRoot']}=...";
					if( !empty($param['defaultValue']) ) echo " (default: {$param['defaultValue']})";
					if( !empty($param['description']) ) echo " ; {$param['description']}";
					echo "\n";
				}
				exit(0);
			} else if( $arg == '--lazy' ) {
				$runOptions['beLazy'] = true;
			} else {
				fwrite(STDERR, "Error: Unrecognized argument: ${arg}\nTry --help for help.\n");
			}
		}
		$unspecifiedParams = array();
		foreach( $paramMetadata as $param ) {
			if( !isset($params[$param['name']]) ) {
				$requiresValue = isset($param['requiresValue']) ? $param['requiresValue'] : true;
				if( isset($param['defaultValue']) ) {
					$params[$param['name']] = $param['defaultValue'];
				} else if( $requiresValue ) {
					$unspecifiedParams[] = $param['name'];
				}
			}
		}
		if( count($unspecifiedParams) ) {
			fwrite(STDERR, "Error: No values given (and no defaults) for the following parameters: ".implode(", ", $unspecifiedParams)."\n");
			exit(1);
		}
		if( $params['reportQuantities'] == 'standard-stuff' ) {
			$params['reportQuantities'] = 'coal,stone,iron-ore,copper-ore,crude-oil,uranium-ore,biter-spawner,spitter-spawner';
		}
		return $this->factorioRunner->normalizeParams($params);
	}

	protected $selfCommitId = null;
	public function getTfmpmCommitId() {
		$selfDir = $this->registry->getProjectRootDir();
		$selfGitDir = "$selfDir/.git";
		if( $this->selfCommitId == null ) {
			$selfCommitShellCmd = "git --git-dir=".escapeshellarg($selfGitDir)." rev-parse HEAD";
			$this->selfCommitId = trim(`$selfCommitShellCmd`);
			if( $this->selfCommitId === '' ) {
				fwrite(STDERR, "Warning: Unable to determine own commit ID\n");
			}
		}
		return $this->selfCommitId == '' ? null : $this->selfCommitId;
	}
}
