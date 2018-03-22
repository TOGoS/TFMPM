<?php

class TFMPM_NormalStorageHelper
extends TFMPM_Component
implements TFMPM_StorageHelper, TFMPM_QueryHelper
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
		
		if( !is_string($keyBy) ) throw new Exception("keyBy parameter to queryRows, if specified must be a string.");
		
		$keyed = array();
		foreach( $data as $r ) {
			$keyed[$r[$keyBy]] = $r;
		}
		return $keyed;
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
	public function queryValueMap($sql, array $params=[]) {
		$map = [];
		foreach( $this->_queryRows($sql, $params) as $row ) {
			if( count($row) != 2 ) {
				throw new Exception("Query returns ".count($row)." columns; queryValueMap expects exactly 2: $sql");
			}
			$kv = array_values($row);
			$map[$kv[0]] = $kv[1];
		}
		return $map;
	}

	protected $transactionLevel = 0;
	protected $subTransactionsFailed = false;
	public function beginTransaction() {
		if( $this->transactionLevel++ == 0 ) {
			$this->sqlRunner->doRawQuery("BEGIN TRANSACTION");
			$this->subTransactionsFailed = false;
		} else {
			$this->sqlRunner->doRawQuery("SAVEPOINT nest{$this->transactionLevel}");
		}
	}
	public function endTransaction($success) {
		$oldLevel = $this->transactionLevel;
		if( --$this->transactionLevel == 0 ) {
			if( $this->subTransactionsFailed ) $success = false;
			if( $success ) {
				$this->sqlRunner->doRawQuery("COMMIT TRANSACTION");
			} else {
				$this->sqlRunner->doRawQuery("ROLLBACK TRANSACTION");
			}
		} else {
			if( $success ) {
				$this->sqlRunner->doRawQuery("RELEASE SAVEPOINT nest{$oldLevel}");
			} else {
				$this->sqlRunner->doRawQuery("ROLLBACK TO SAVEPOINT nest{$oldLevel}");
			}
		}
	}
	
	//// DB <-> internal form transforms
	
	//// Parameter parsing/translation to CMIPREST classes

	protected $neededEntityIds = 0;
	public function preallocateEntityIds($count) {
		$this->neededEntityIds += $count;
	}
	
	protected $entityIdPool = [];
	/** Add $count new entity IDs to $this->entityIdPool */
	protected function allocateEntityIds($count) {
		$this->entityIdPool = array_merge($this->entityIdPool, $this->queryValueSet(
			"SELECT nextval({seq}) AS id\n".
			"FROM generate_series(1,{count})", [
				'count'=>$count, 'seq'=>'tfmpm.newentityid'
			]));
	}
	
	protected function finishPreallocatingEntityIds() {
		$this->allocateEntityIds($this->neededEntityIds);
		$this->neededEntityIds = 0;
	}
	
	public function newEntityId() {
		if( $this->neededEntityIds < 1 ) $this->preallocateEntityIds(1);
		$this->finishPreallocatingEntityIds();
		return array_shift($this->entityIdPool);
	}

	public function insertNewItems($rc, array $itemData) {
		$this->storage->saveItems( $itemData, $this->rc($rc) );
	}
	
	public function insertNewItem($rc, array $itemData) {
		$this->insertNewItems($rc, [$itemData]);
	}
	
	protected function _upsertItem($rc, array $itemData, $resultNeeded) {
		$rc = $this->rc($rc);
		$items = $this->storage->saveItems([$itemData], $rc, array(
			EarthIT_STorage_ItemSaver::RETURN_SAVED => $resultNeeded,
			EarthIT_STorage_ItemSaver::ON_DUPLICATE_KEY => EarthIT_STorage_ItemSaver::ODK_UPDATE,
		));
		if( $resultNeeded ) return EarthIT_Storage_Util::first($items);
	}
	
	/** @override */
	public function upsertItem($rc, array $itemData) {
		$this->_upsertItem($rc, $itemData, false);
	}
	
	/** @override */
	public function postItem($rc, array $itemData) {
		return $this->_upsertItem($rc, $itemData, true);
	}
	
	public function getItemById($rc, $itemId) {
		$rc = $this->rc($rc);
		return EarthIT_CMIPREST_Util::getItemById($this->storage, $rc, $itemId);
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
		return $this->sqlGenerator->dbExternalToSchemaItems($rows, $rc);
	}
	
	/**
	 * Fetch a list of items matching the given filters.
	 *
	 * @param array $filters an array filters (see class documentation)
	 * @param array $orderBy list of fields to order by, optionally prefixed with '+' or '-'
	 */
	public function getItems($rc, array $filters=[], array $orderBy=[], array $withs=[], $skip=0, $limit=null, array $options=array()) {
		$rc = $this->rc($rc);
		$sp = EarthIT_Storage_Util::makeSearch($rc, $filters, $orderBy, $skip, $limit, [
			EarthIT_Storage::SCHEMA => $this->schema,
			EarthIT_Storage::FUZZY_MATCH => false,
		]);
		$namer = function($obj, $plural=false) {
			$n = $obj->getName();
			return $plural ? EarthIT_Schema_WordUtil::pluralize($n) : $n;
		};
		$johnBranches = EarthIT_CMIPREST_RequestParser_Util::withsToJohnBranches(
			$this->schema, $rc, $withs, $namer);
		$johnResults = $this->storage->johnlySearchItems($sp, $johnBranches, $options);
		$johnCollections = TFMPM_JohnResultAssemblyUtil::collectJohns($johnBranches, 'root');
		return TFMPM_JohnResultAssemblyUtil::assembleMultiItemResult($rc, $johnCollections, $johnResults);
	}
	
	/**
	 * Return the first item returned by getItems($rc, $filters, $orderBy);
	 */
	public function getItem($rc, array $filters=[], array $orderBy=[], array $withs=[], $skip=0, array $options=array()) {
		foreach( $this->getItems($rc, $filters, $orderBy, $withs, $skip, 1, $options) as $item ) return $item;
		return null;
	}
	
	/**
	 * Delete all items from the given class matching the given filters.
	 */
	public function deleteItems($rc, array $filters=[]) {
		$rc = $this->rc($rc);
		$this->storage->deleteItems($rc, EarthIT_Storage_ItemFilters::parseMulti($filters, $rc));
	}
}
