<?php

class TFMPM_MapModel extends TFMPM_Component
{
	public function getDatabaseSummary() {
		$totalGenerations = $this->storageHelper->queryValue("SELECT COUNT(*) FROM map_generation");

		return array(
			'mapGenerationCount' => $totalGenerations,
		);
	}

	protected function getFieldFilterability(EarthIT_Schema_Field $field) {
		return TFMPM_Util::toSet($field->getPropertyValues("http://ns.nuke24.net/TFMPM/filterability"));
	}
	
	public function queryParamsToMapFilters(array $queryParams) {
		$mapRc = $this->schema->getResourceClass('map generation');
		$filters = array();
		foreach( $mapRc->getFields() as $fieldName=>$field ) {
			$filterability = $this->getFieldFilterability($field);
			if( isset($filterability['exact-match']) ) {
				$fieldCode = EarthIT_Schema_WordUtil::toCamelCase($fieldName);
				if( isset($queryParams[$fieldCode]) ) {
					$filters[$fieldCode] = $queryParams[$fieldCode];
				}
			}
			// todo: if date range, check for lt/gt options
		}
		return $filters;
	}
	
	protected function filtersToWhereSqls(array $filters, $alias, EarthIT_Schema_ResourceClass $rc, EarthIT_DBC_ParamsBuilder $PB) {
		$columnByFieldCode = array();
		$columnNamer = $this->dbObjectNamer;
		foreach( $rc->getFields() as $fieldName=>$field ) {
			$fieldCode = EarthIT_Schema_WordUtil::toCamelCase($fieldName);
			$columnName = $columnNamer->getColumnName($rc, $field);
			$columnByFieldCode[$fieldCode] = $columnName;
		}

		$wheres = array();
		foreach( $filters as $k=>$filter ) {
			if( $k == 'mapOffset' ) {
				$coordWheres = array();
				foreach( $filter as $coord ) {
					list($x,$y) = TFMPM_Util::parseCoordinate($coord);
					$coordWheres[] = "{$alias}.map_offset_x = $x AND {$alias}.map_offset_y = $y";
				}
				$wheres[] = "(".implode(" OR ",$coordWheres).")";
			} else {
				if( !isset($columnByFieldCode[$k]) ) throw new Exception("Unrecognized field name: $k");
				$leftSql = "{$alias}.{$columnByFieldCode[$k]}";
				$rightValue = $filter;
				$wheres[] = "{$leftSql} IN {".$PB->bind($rightValue)."}";
			}
		}
		return $wheres;
	}
	
	protected static function whereClause(array $wheres) {
		if(count($wheres) == 0) return array();
		
		return "WHERE ".implode("\n  AND ", $wheres)."\n";
	}
	
	public function getMapFilterMetadata(array $filters) {
		$mapRc = $this->schema->getResourceClass('map generation');
		$mapGenFields = $mapRc->getFields();
		$availableFilters = array();
		$columnNamer = $this->dbObjectNamer;
		$PB = new EarthIT_DBC_ParamsBuilder();
		$filterWheres = $this->filtersToWhereSqls($filters, 'mapgen', $mapRc, $PB);
		foreach( $mapGenFields as $fieldName => $field ) {
			$filterability = $this->getFieldFilterability($field);
			$columnName = $columnNamer->getColumnName($mapRc, $field);
			$fieldCode = EarthIT_Schema_WordUtil::toCamelCase($fieldName);
			$filterInfo = array(
				'fieldName' => $fieldName,
				'fieldCode' => $fieldCode,
				'filterability' => $filterability
			);
			if( isset($filterability['exact-match']) ) {
				if( $fieldCode == 'mapOffset' ) {
					$values = $this->storageHelper->queryValueSet(
						"SELECT DISTINCT mapgen.map_offset_x || ',' || mapgen.map_offset_y\n".
						"FROM map_generation AS mapgen\n".
						self::whereClause(array_merge(
							array("map_offset_x IS NOT NULL AND map_offset_y IS NOT NULL"),
							$filterWheres
						)),
						$PB->getParams()
					);
				} else {
					$values = $this->storageHelper->queryValueSet(
						"SELECT DISTINCT(mapgen.\"$columnName\")\n".
						"FROM map_generation AS mapgen\n".
						self::whereClause(array_merge(
							array("mapgen.$columnName IS NOT NULL"),
							$filterWheres
						)),
						$PB->getParams()
					);
				}
				switch( $field->getType()->getName() ) {
				case 'Git commit ID':
					sort($values);
					break;
				case 'number':
					//$values = array_map(function($v) { return (float)$v; }, $values);
					asort($values);
					break;
				default:
					natsort($values);
					break;
				}
				$filterInfo['values'] = TFMPM_Util::toSet($values);
			}
			$availableFilters[$fieldCode] = $filterInfo;
		}
		
		return $availableFilters;
	}
	
	/**
	 * Get all maps matching the criteria, along with
	 * @param array $filters - see StorageHelper documentation
	 */
	public function getMaps(array $filters) {
		$mapRc = $this->schema->getResourceClass('map generation');
		$selects = array();
		$columnNamer = $this->dbObjectNamer;
		foreach( $mapRc->getFields() as $fieldName=>$field ) {
			if( $field->getFirstPropertyValue('http://ns.nuke24.net/Schema/Application/hasADatabaseColumn',true) === false ) continue;
			$columnName = $columnNamer->getColumnName($mapRc, $field);
			$fieldCode = EarthIT_Schema_WordUtil::toCamelCase($fieldName);
			$selects[] = "mapgen.\"$columnName\" AS \"$fieldCode\"";
		}
		$PB = new EarthIT_DBC_ParamsBuilder();
		$wheres = $this->filtersToWhereSqls($filters, 'mapgen', $mapRc, $PB);
		$whereClause = count($wheres) ? "WHERE ".implode("\n  AND ",$wheres)."\n" : "";
		$sql =
			"SELECT\n\t".implode(",\n\t", $selects)."\n".
			"FROM map_generation AS mapgen\n".
			$whereClause;
		$rows = $this->storageHelper->queryRows($sql, $PB->getParams());
		$maps = array();
		foreach( $rows as $row ) {
			$map = $row;
			$map['mapOffset'] = array($row['mapOffsetX'], $row['mapOffsetY']);
			unset($row['mapOffsetX']);
			unset($row['mapOffsetY']);
			$maps[$row['generationId']] = $map;
		}
		return $maps;
	}
}
