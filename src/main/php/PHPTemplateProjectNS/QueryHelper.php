<?php

/**
 * Interface for conveniently doing common database-related tasks.
 *
 * Some implementations may actually build up a list of queries
 * instead of immediately executing them, in which case methods that
 * can't be done without actually running the query (e.g. queryRows)
 * should throw exceptions.
 *
 * All functions that take $sql, $params=[] can alternatively take a
 * single EarthIT_DBC_SQLExpression argument.
 *
 * Any 'rows' returned are keyed by result column names (i.e. column
 * names or identifiers specified by 'AS "foo"' in the SELECT).
 */
interface PHPTemplateProjectNS_QueryHelper
{
	/**
	 * Execute some SQL, returning no result
	 */
	public function doQuery($sql, $params=[]);
	/**
	 * Return a single row.
	 * Returns null if no rows are returned.
	 */
	public function queryRow($sql, array $params=[]);
	/**
	 * keyBy may be:
	 *
	 * - null, in which case keys will be sequential integers
	 * - the name of a single result column, in which case rows will be
    *   keyed by that column's value (this is equivalent to passing an
    *   array containing just that column name)
	 * - a list of several result column names, in which case the
    *   return value will have nested arrays for each level
	 */
	public function queryRows($sql, array $params=[], $keyBy=null);
	public function queryValue($sql, array $params=[]);
	public function queryValueSet($sql, array $params=[]);

	public function beginTransaction();
	public function endTransaction($success);
}
