<?php

class PHPTemplateProjectNS_NormalStorageHelper implements PHPTemplateProjectNS_StorageHelper
{
	protected $sqlRunner;
	protected $schema;
	protected $dbObjectNamer;
	
	public function __construct(
		EarthIT_DBC_SQLRunner $sqlRunner,
		EarthIT_Schema $schema,
		EarthIT_DBC_Namer $dbObjectNamer
	) {
		$this->sqlRunner = $sqlRunner;
		$this->schema = $schema;
		$this->dbObjectNamer = $dbObjectNamer;
	}
	
	////
										  
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

	/**
	 * Insert new items.
	 * Use only when you know that there will be no key collisions.
	 * If the item already exists, this will probably result in an error.
	 * Returns nothing.
	 */
	public function insertNewItems($rc, array $itemData) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
	/**
	 * Insert a single new item.  Suggested implementation is just to call insertNewItems($rc, [$itemData]);
	 */
	public function insertNewItem($rc, array $itemData) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
	/**
	 * Insert a new item or update it if it doesn't already exist.
	 */
	public function upsertItem($rc, array $itemData) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
	/**
	 * Fetch a list of items matching the given filters.
	 *
	 * @param array $filters an array filters (see class documentation)
	 * @param array $orderBy list of fields to order by, optionally prefixed with '+' or '-'
	 */
	public function getItems($rc, array $filters=[], array $orderBy=[]) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
	/**
	 * Return the first item returned by getItems($rc, $filters, $orderBy);
	 */
	public function getItem($rc, array $filters=[], array $orderBy=[]) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
	/**
	 * Delete all items from the given class matching the given filters.
	 */
	public function deleteItems($rc, array $filters=[]) {
		throw new Exception(get_class($this).'#'.__FUNCTION__." not yet implemented!");
	}
}
