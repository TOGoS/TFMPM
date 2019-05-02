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
			$names = array($k);
			if( isset($info['aliases']) ) {
				foreach( $info['aliases'] as $a ) $names[] = $a;
			}
			$info['name'] = EarthIT_Schema_WordUtil::toCamelCase($k);
			$argRoot = "--".EarthIT_Schema_WordUtil::toKebabCase($k);
			$info['argRoot'] = $argRoot;
			foreach( $names as $name ) {
				$argRoot = "--".EarthIT_Schema_WordUtil::toKebabCase($name);
				$re = "/^$argRoot=(.*)$/";
				$paramReMap[$re] = $info;
			}
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

	public function generateMapPreview($params, $runOptions) {
		$selfGitDir = "{$this->registry->projectRootDir}/.git";
		$blobRepoDir = getenv('HOME')."/.ccouch";
		$metalogDir = "{$this->registry->projectRootDir}/logs";
		if( !is_dir($metalogDir) ) mkdir($metalogDir, 0777, true);
		
		$runner = $this->factorioRunner;
		$systemUtil = $this->systemUtil;
		
		if( $runOptions['beLazy'] ) {
			$filters = array();
			foreach( $params as $k=>$v ) {
				if( $k == 'reportQuantities' ) continue; // Can't filter on that!
				$filters[$k] = array($v);
			}
			$mapGenerations = $this->mapModel->getMaps($filters);
			if( count($mapGenerations) > 0 ) {
				if( $runOptions['verbosity'] >= 200 ) {
					fwrite(STDERR, "Skipping; already generated: ");
					fwrite(STDERR, json_encode($params,JSON_PRETTY_PRINT).": ");
					fwrite(STDERR, json_encode($mapGenerations,JSON_PRETTY_PRINT)."\n");
				} else if( $runOptions['verbosity'] >= 100 ) {
					fwrite(STDERR, "Skipping; already generated!\n");
				}
				foreach( $mapGenerations as $mapGeneration ) {
					return array(
						'startTime' => $mapGeneration['generationStartTime'],
						'endTime' => $mapGeneration['generationEndTime'],
						'tfmpmCommitId' => $mapGeneration['tfmpmCommitId'],
						'generationParams' => $params,
						'generationResult' => array(
							'mapFile' => $mapGeneration['mapImageUrn'],
							'logFile' => $mapGeneration['logUrn'],
						),
					);
				}
			}
		}
		
		$selfCommitId = $this->getTfmpmCommitId();
		
		$metalogFile = $metalogDir."/".date('Y_m_d').".jsonl";
		$systemUtil->mkparentDirs($metalogFile);
		
		$startTime = date('c');
		$result = $runner->generateMapPreview($params);
		$endTime = date('c');
		$metalogRecord = array(
			'startTime' => $startTime,
			'endTime' => $endTime,
			'tfmpmCommitId' => $selfCommitId,
			'generationParams' => $params,
			'generationResult' => $result,
		);
		
		// ".jsonl" extension as recommended by http://jsonlines.org/
		
		$metalogStream = fopen( $metalogFile, "ab" );
		fwrite($metalogStream, json_encode($metalogRecord)."\n");
		fclose($metalogStream);
		
		if( $runOptions['verbosity'] >= 100 ) {
			echo json_encode($metalogRecord,JSON_PRETTY_PRINT), "\n";
		}
		
		$this->mapRecordInserter->open();
		$this->mapRecordInserter->item($metalogRecord);
		$this->mapRecordInserter->close();
		return $metalogRecord;
	}
}
