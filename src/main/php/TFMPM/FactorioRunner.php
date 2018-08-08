<?php

class TFMPM_FactorioRunner extends TFMPM_Component
{
	protected $storeSector = 'factorio-map-previews';
	
	protected static function requireParam(array $params, $key) {
		if( !isset($params[$key]) ) {
			throw new Exception("Required parameter '$key' not provided: ".print_r($params,true));
		}
		return $params[$key];
	}

	public static function normalizeParams(array $params) {
		if( !isset($params['mapOffset']) ) $params['mapOffset'] = array(0,0);
		if( is_string($params['mapOffset']) ) $params['mapOffset'] = explode(',', $params['mapOffset']);
		if( isset($params['reportQuantities']) ) {
			if( $params['reportQuantities'] === '' or $params['reportQuantities'] === array() ) {
				unset($params['reportQuantities']);
			} else if( is_string($params['reportQuantities']) ) {
				$params['reportQuantities'] = explode(',', $params['reportQuantities']);
			}
		}
		return $params;
	}

	/**
	 * @param $params array of
	 *   factorioCommitId
	 *   dataCommitId
	 *   mapGenSeed
	 *   mapScale -- meters per pixel
	 *   mapOffset -- x,y offset of center of map
	 *   mapWidth -- width (and height) of map, in pixels
	 *   reportQuantities -- list of prototype names for which to request placement quantity info
	 *   slopeShading
	 * @return array of
	 *   mapUrn -- URN of map image
	 *   logUrn -- URN of log file (from which you can extract resource counts, generator run time, etc)
	 */
	public function generateMapPreview( array $params ) {
		$storeOptions = array(TOGoS_PHPN2R_Repository::OPT_SECTOR => $this->storeSector);
		$logFile = $this->primaryBlobRepository->newTempFile($storeOptions);
		$mapFile = $this->primaryBlobRepository->newTempFile($storeOptions + array(
			'postfix' => '.png'
		));
		if( !preg_match('/\.png$/', $mapFile) ) {
			throw new Exception("Map temp file doesn't end with '.png': $mapFile");
		}
		$mapDir = dirname($mapFile);
		$mapBasename = basename($mapFile);
		
		$factorioCommitId = self::requireParam($params, 'factorioCommitId');
		$dataCommitId = isset($params['dataCommitId']) ? $params['dataCommitId'] : $factorioCommitId;
		$mapOffset = isset($params['mapOffset']) ? $params['mapOffset'] : '0,0';
		if( is_array($mapOffset) ) $mapOffset = implode(',', $mapOffset);
		$mapWidth = isset($params['mapWidth']) ? $params['mapWidth'] : 1024;
		$reportQuantities = isset($params['reportQuantities']) ? $params['reportQuantities'] : array();
		$slopeShading = isset($params['slopeShading']) ? $params['slopeShading'] : 0;

		$factorioDockerImageId = $this->factorioBuilder->ensureFactorioHeadlessDockerImageExists($factorioCommitId);

		$dataDir = null;
		if( $factorioCommitId != $dataCommitId ) {
			$dataDir = $this->factorioBuilder->checkOutFactorioHeadlessData($dataCommitId)."/data";
			$dataDir = realpath($dataDir); // So that Docker will accept it
		}

		// Different images allow (non-package builds) or require (package builds)
		// data to be in different places.
		// So here we'll try to figure out which kind of image this is.
		$imageInfo = $this->dockerImageMetadataCache->getImageMetadata($factorioDockerImageId);
		$explicitWorkingDir = null;
		if( isset($imageInfo['Config']['Labels']['factorio_data_directory']) ) {
			$containedFactorioDataDir = $imageInfo['Config']['Labels']['factorio_data_directory'];
		} else if( isset($imageInfo['Config']['WorkingDir']) ) {
			$containedFactorioDataDir = $imageInfo['Config']['WorkingDir'] . "/data";
		} else {
			$explicitWorkingDir = "/opt/bin/Factorio";
			$containedFactorioDataDir = $explicitWorkingDir . "/data";
		}
		
		
		$cmdArgs = array("docker","run");
		$cmdArgs[] = "-v";
		$cmdArgs[] = "{$mapDir}:/mnt/map-previews";
		if( $dataDir ) {
			$cmdArgs[] = "-v";
			$cmdArgs[] = "{$dataDir}:{$containedFactorioDataDir}";
		}
		if( $explicitWorkingDir ) {
			$cmdArgs[] = "-w";
			$cmdArgs[] = $explicitWorkingDir;
		}
		$cmdArgs[] = $factorioDockerImageId;
		// Factorio arguments
		$cmdArgs[] = "--generate-map-preview=/mnt/map-previews/{$mapBasename}";
		$cmdArgs[] = "--map-gen-seed=".self::requireParam($params,'mapSeed');
		$cmdArgs[] = "--map-preview-scale=".self::requireParam($params,'mapScale');
		$cmdArgs[] = "--map-preview-offset=$mapOffset";
		$cmdArgs[] = "--map-preview-size=$mapWidth";
		if( count($reportQuantities) ) {
			$cmdArgs[] = "--report-quantities=".implode(',',$reportQuantities);
		}
		$cmdArgs[] = "--slope-shading=$slopeShading";

		$this->systemUtil->runCommand($cmdArgs, array(
			'outputFile' => $logFile,
			'errorFd' => '1',
		));
		$files = array('logFile'=>$logFile, 'mapFile'=>$mapFile);
		$result = array();
		foreach( $files as $k => $file ) {
			if( file_exists($file) ) {
				$result[$k] = $this->primaryBlobRepository->putTempFile($file, $this->storeSector);
			}
		}
		return $result;
	}
}
