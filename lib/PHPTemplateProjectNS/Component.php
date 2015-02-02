<?php

class PHPTemplateProjectNS_Component
{
	protected $registry;
	public function __construct( PHPTemplateProjectNS_Registry $reg ) {
		$this->registry = $reg;
	}
	
	//// Shortcuts for database commands
	
	protected function doQuery( $sql, $params=[] ) {
		// This function is pretty much alias for queryRows.
		// Use it when you don't want to imply any return value.
		$this->queryRows($sql, $params);
	}
	/**
	 * $queries being a list of [sql, parameters]
	 */
	protected function doQueriesAtomically( array $queries ) {
		$this->doQuery('START TRANSACTION');
		try {
			foreach( $queries as $q ) {
				list($sql,$params) = $q;
				$this->doQuery( $sql, $params );
			}
			$this->doQuery('COMMIT TRANSACTION');
		} catch( Exception $e ) {
			$this->doQuery('ROLLBACK TRANSACTION');
			throw $e;
		}
	}
	protected function queryRows( $sql, $params=[], $keyBy=null ) {
		$rows = $this->registry->sqlRunner->fetchRows($sql, $params);
		if( $keyBy === null ) return $rows;
		$keyed = [];
		foreach( $rows as $row ) {
			$keyed[$row[$keyBy]] = $row;
		}
		return $keyed;
	}
	protected function queryValue( $sql, $params=[] ) {
		foreach( $this->queryRows($sql,$params) as $row ) {
			foreach( $row as $v ) return $v;
		}
		return null;
	}
	protected function queryValueSet( $sql, $params=[] ) {
		$set = [];
		foreach( $this->queryRows($sql,$params) as $row ) {
			foreach( $row as $v ) $set[$v] = $v;
		}
		return $set;
	}
	protected function queryValueMap( $sql, $params=[] ) {
		$map = [];
		foreach( $this->queryRows($sql,$params) as $row ) {
			$values = array_values($row);
			$map[$values[0]] = $values[1];
		}
		return $map;
	}
	protected function queryRow( $sql, $params=[] ) {
		foreach( $this->queryRows($sql,$params) as $row ) return $row;
		return null;
	}
	
	////
	
	protected function rc( $rc ) {
		if( is_string($rc) ) return $this->schema->getResourceClass($rc);
		if( $rc instanceof EarthIT_Schema_ResourceClass ) return $rc;
		throw new Exception("Invalid resource class or resource class name: ".var_export($rc,true));
	}
	
	/**
	 * Shortcut to get objects from the registry.  This has to be
	 * public because that's how __get works, but it's not intended to
	 * be used from outside.
	 */
	public function __get($attrName) {
		return $this->registry->$attrName;
	}
}
