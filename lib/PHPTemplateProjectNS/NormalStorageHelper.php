<?php

class PHPTemplateProjectNS_NormalStorageHelper
extends PHPTemplateProjectNS_Component
implements PHPTemplateProjectNS_StorageHelper, PHPTemplateProjectNS_QueryHelper
{
	public function doQuery($sql, $params=[]) {
		list($sql,$params) = EarthIT_DBC_SQLExpressionUtil::templateAndParamValues($sql, $params);
		$this->sqlRunner->doQuery($sql, $params);
	}
	
	protected function _queryRows($sql, array $params=[]) {
		list($sql,$params) = EarthIT_DBC_SQLExpressionUtil::templateAndParamValues($sql, $params);
		return $this->sqlRunner->fetchRows($sql, $params);
	}
	
	public function queryRow($sql, array $params=[]) {
		foreach( $this->_queryRows($sql,$params) as $row ) return $row;
	}
	
	public function queryRows($sql, array $params=[], $keyBy=null) {
		$data = $this->_queryRows($sql, $params);
		if( $keyBy === null ) return $data;
		
		$keyed = [];
	}
	public function queryValue($sql, array $params=[]) {
		foreach( $this->_queryRows($sql,$params) as $row ) {
			foreach( $row as $v ) return $v;
		}
		return null;
	}
	public function queryValueSet($sql, array $params=[]) {
		$set = [];
		foreach( $this->_queryRows($sql, $params) as $row ) {
			if( count($row) > 1 ) {
				throw new Exception("Query returns more than one column; queryValueSet is ambiguous: $sql");
			}
			foreach( $row as $v ) $set[$v] = $v;
		}
		return $set;
	}
	
	public function beginTransaction() {
		$this->sqlRunner->doRawQuery("START TRANSACTION");
	}
	public function endTransaction($success) {
		if( $success ) {
			$this->sqlRunner->doRawQuery("COMMIT TRANSACTION");
		} else {
			$this->sqlRunner->doRawQuery("ROLLBACK TRANSACTION");
		}
	}
	
	//// DB <-> internal form transforms
	// TODO: Maybe make these a public part of the Storage API
	
	protected static function dbToPhpValue( EarthIT_Schema_DataType $t, $value ) {
		// Various special rules may end up here
		return EarthIT_CMIPREST_Util::cast( $value, $t->getPhpTypeName() );
	}
	
	protected function dbObjectToInternal( EarthIT_Schema_ResourceClass $rc, array $obj ) {
		$fieldValues = array();
		foreach( EarthIT_CMIPREST_Util::storableFields($rc) as $f ) {
			$fieldName = $f->getName();
			$columnName = $this->dbNamer->getColumnName($rc, $f);
			if( isset($obj[$columnName]) ) {
				$fieldValues[$f->getName()] = self::dbToPhpValue($f->getType(), $obj[$columnName]);
			}
		}
		return $fieldValues;
	}
	
	//// Parameter parsing/translation to CMIPREST classes

	protected function orderBys( array $orderBySpecs ) {
		if( count($orderBySpecs) == 0 ) return [];
		throw new Exception("Order-by parsing not yet implemented.");
	}
	
	protected function fieldMatchers( EarthIT_Schema_ResourceClass $rc, array $fieldValues ) {
		$fieldsByName = $rc->getFields();
		$matchers = array();
		foreach( $fieldValues as $k => $value ) {
			if( isset($fieldsByName[$k]) ) {
				$fn = $k;
			} else if( isset($fieldsByName[$k]) ) {
				$fn = $fieldsByName[$k]->getName();
			} else {
				throw new Exception("'".ucfirst($rc->getName())."' has no such field as '$k'.");
			}
			
			if( $value instanceof EarthIT_CMIPREST_FieldMatcher ) {
				$matchers[$fn] = $value;
			} else if( is_array($value) ) {
				$matchers[$fn] = new EarthIT_CMIPREST_FieldMatcher_In($value);
			} else if( is_scalar($value) ) {
				$matchers[$fn] = new EarthIT_CMIPREST_FieldMatcher_Equal($value);
			} else {
				throw new Exception("Don't know how to make field matcher from ".var_export($value,true));
			}
		}
		return $matchers;
	}
	
	//

	/**
	 * Insert new items.
	 * Use only when you know that there will be no key collisions.
	 * If the item already exists, this will probably result in an error.
	 * Returns nothing.
	 */
	public function insertNewItems($rc, array $itemData) {
		// TODO: Better.
		foreach( $itemData as $itemDat ) {
			$this->storage->postItem($this->rc($rc), $itemDat);
		}
	}
	/**
	 * Insert a single new item.  Suggested implementation is just to call insertNewItems($rc, [$itemData]);
	 */
	public function insertNewItem($rc, array $itemData) {
		$this->insertNewItems($rc, [$itemData]);
	}
	
	protected function _upsertItem($rc, array $itemData, $resultNeeded) {
		$rc = $this->rc($rc);
		$itemId = EarthIT_CMIPREST_Util::itemId($rc, $itemData);
		if( $itemId === null ) {
			return $this->storage->postItem($rc, $itemData);
		} else {
			return $this->storage->patchItem($rc, $itemId, $itemData);
		}
	}
	
	/** @override */
	public function upsertItem($rc, array $itemData) {
		$this->_upsertItem($rc, $itemData, false);
	}

	/**
	 * Fetch a bunch of items from a query.
	 * Any transformations that need to be done on the query must be included in the SQL.
	 * (e.g. geometry to GeoJSON)
	 * Queried columns should otherwise be database-form (e.g. SELECT * FROM foo).
	 */
	public function queryItems($rc, $sql, array $params=[]) {
		$rc = $this->rc($rc);
		$rows = $this->queryRows($sql, $params);
		$items = [];
		foreach( $rows as $row ) {
			$items[] = $this->dbObjectToInternal($rc, $row);
		}
		return $items;
	}
	/**
	 * Fetch a list of items matching the given filters.
	 *
	 * @param array $filters an array filters (see class documentation)
	 * @param array $orderBy list of fields to order by, optionally prefixed with '+' or '-'
	 */
	public function getItems($rc, array $filters=[], array $orderBy=[]) {
		$rc = $this->rc($rc);
		$orderBy = $this->orderBys($orderBy);
		$sp = new EarthIT_CMIPREST_SearchParameters(
			$this->fieldMatchers($rc, $filters),
			$orderBy, 0, null
		);
		$searchRes = $this->storage->search($rc, $sp, []);
		return $searchRes['root'];
	}
	/**
	 * Return the first item returned by getItems($rc, $filters, $orderBy);
	 */
	public function getItem($rc, array $filters=[], array $orderBy=[]) {
		foreach( $this->getItems($rc, $filters, $orderBy) as $item ) return $item;
		return null;
	}
	/**
	 * Delete all items from the given class matching the given filters.
	 */
	public function deleteItems($rc, array $filters=[]) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
}
