<?php

class TFMPM_MapRecordInserter extends TFMPM_Component
{
	protected function buildInsertQuery( $tableName, $values ) {
		$params = array();
		$columnNames = array();
		$valueTemplates = array();
		foreach($values as $k=>$v) {
			if( $k[0] == '$' ) continue;
			$columnNames[] = $k;
			$valueTemplates[] = "{{$k}}";
			$params[$k] = $v;
		}
		$sql = "INSERT INTO $tableName (".implode(', ',$columnNames).") VALUES (".implode(', ',$valueTemplates).")";
		return EarthIT_DBC_SQLExpressionUtil::expression($sql, $params);
	}

	protected function buildInsertQueries( $things ) {
		$z = array();
		foreach( $things as $t ) {
			if( !isset($t['$table']) ) throw new Exception("Knead uh doll hair tae bull: ".print_r($t, true));
			$z[] = $this->buildInsertQuery($t['$table'], $t);
		}
		return $z;
	}

	protected static function normalize( $value ) {
		if( is_array($value) ) {
			ksort($value);
			foreach( $value as $k=>$v ) {
				$v = self::normalize($v);
				if( $v == null ) unset($value[$k]);
				else $value[$k] = $v;
			}
		}
		return $value;
	}

	protected static function findValue($arr, $keys, $defaultValue=null) {
		if( !is_array($keys) ) $keys = array($keys);
		foreach( $keys as $k ) if(isset($arr[$k])) return $arr[$k];
		return $defaultValue;
	}
	
	protected function munge( array $info, array $metadata ) {
		// Old generator recorded a \n after its own commit ID :P
		if( isset($info['tfmpmCommitId']) ) $info['tfmpmCommitId'] = trim($info['tfmpmCommitId']);
		$encoded = json_encode(self::normalize($info));
		$id = hash('sha1', $encoded);
		$genParams = $info['generationParams'];
		$genResult = $info['generationResult'];
		$mapScale = self::findValue($genParams, 'mapScale');
		$mapWidth = self::findValue($genParams, 'mapWidth');
		$mapOffset = self::findValue($genParams, 'mapOffset');
		$slopeShading = self::findValue($genParams, 'slopeShading');
		$reportQuantities = self::findValue($genParams, 'reportQuantities', array());
		if( $mapOffset === null ) $mapOffset = array(0,0);
		if( is_string($mapOffset) ) $mapOffset = explode(',',$mapOffset);
		$logFileUrn = self::findValue($genResult, 'logFile');
		$compilationReportedElapsedTime = null;
		$generationReportedElapsedTime = null;
		$resourceStats = array();
		if( $logFileUrn ) {
			$log = $this->blobRepository->getBlob($logFileUrn);
			if( $log !== null ) {
				$lines = explode("\n", (string)$log);
				foreach( $lines as $line ) {
					if( preg_match('/MapGenSettings compilation took (\d+(?:\.\d+)?) seconds/', $line, $bif) ) {
						$compilationReportedElapsedTime = $bif[1];
					} else if( preg_match('/(\S+): (total:\d.*)$/', $line, $bif) ) {
						$resourceName = $bif[1];
						$stats = array();
						foreach( preg_split('/,\s*/', $bif[2]) as $thing ) {
							$kv = explode(':',$thing,2);
							if( count($kv) < 2 ) continue;
							$stats[trim($kv[0])] = trim($kv[1]);
						}
						$resourceStats[$resourceName] = $stats;
					} else if( preg_match('/Map preview generation time: (\d+(?:\.\d+)?) seconds/', $line, $bif) ) {
						$generationReportedElapsedTime = $bif[1];
					}
				}
			}
		}
		$inserts = array(
			array(
				'$table' => 'map_generation',
				'generation_id' => $id,
				'generation_start_time' => self::findValue($info, array('startTime','date')),
				'tfmpm_commit_id' => self::findValue($info, 'tfmpmCommitId'),
				'generator_node_name' => self::findValue($info, 'generatorNodeName'),
				'factorio_commit_id' => self::findValue($genParams, 'factorioCommitId'),
				'data_commit_id' => self::findValue($genParams, array('dataCommitId', 'factorioCommitId')),
				'map_seed' => self::findValue($genParams, 'mapSeed'),
				'map_scale' => $mapScale,
				'map_width' => $mapWidth,
				'map_offset_x' => $mapOffset[0],
				'map_offset_y' => $mapOffset[1],
				'slope_shading' => $slopeShading,
				'report_quantities' => implode(',', $reportQuantities),
				'map_image_urn' => self::findValue($genResult, 'mapFile'),
				'log_file_urn' => $logFileUrn,
				'generation_end_time' => self::findValue($info, 'endTime'),
				'compilation_reported_elapsed_time' => $compilationReportedElapsedTime,
				'generation_reported_elapsed_time' => $generationReportedElapsedTime,
			)
		);
		foreach( $resourceStats as $resourceName=>$stats ) {
			$totalQuantity = self::findValue($stats, 'total');
			$averageQuantity = self::findValue($stats, 'averageQuantity');
			if( $averageQuantity === null and $totalQuantity !== null ) {
				$mapRealWidth = $mapScale * $mapWidth;
				$mapRealArea = $mapRealWidth * $mapRealWidth;
				$averageQuantity = $totalQuantity / $mapRealArea;
			}
			$inserts[] = array(
				'$table' => 'resource_stats',
				'generation_id' => $id,
				'resource_name' => $resourceName,
				'total_quantity' => $totalQuantity,
				'average_quantity' => $averageQuantity,
				'max_unclamped_probability' => self::findValue($stats, 'maxUnclampedProbability'),
				'max_richness' => self::findValue($stats, 'maxRichness'),
				'average_richness' => self::findValue($stats, 'averageRichness'),
			);
		}
		return $inserts;
	}
	
	public function open(array $metadata=array()) { }
	
	public function item($rec, array $metadata=array()) {
		$tabVals = $this->munge($rec, $metadata);
		$inserts = $this->buildInsertQueries($tabVals);
		foreach( $inserts as $insert ) {
			$this->sqlRunner->doQuery($insert);
		}
	}
	
	public function close(array $metadata=array()) { }
}
