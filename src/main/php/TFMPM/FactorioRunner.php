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

		if( $factorioCommitId != $dataCommitId ) {
			throw new Exception("Dat acommit that's != factorio commit not supported: $dataCommitId != $factorioCommitId");
		}

		$factorioDockerImageId = $this->factorioBuilder->ensureFactorioHeadlessDockerImageExists($factorioCommitId);

		# To checkout a data directory...
		# git --git-dir=/home/tog/proj/Factorio/.git --work-tree=/home/tog/proj/TFMPM/factorio-checkouts/7bf41f085f84dca934d0e00e824de29260d854d6 checkout 7bf41f085f84dca934d0e00e824de29260d854d6 data
		# might want to delete image files (*.png) or ignore them somehow...
		
		$cmdArgs = array(
			"docker","run",
			"-v","{$mapDir}:/opt/bin/Factorio/map-previews",
			// Mounting of data dir would go here
			"-w","/opt/bin/Factorio",
			$factorioDockerImageId
		);
		$cmdArgs[] = "--generate-map-preview=map-previews/{$mapBasename}";
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
