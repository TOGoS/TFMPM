<?php

/**
 * Extends QueryHelper to add functions that deal with resource
 * classes and schema form items instead of just doing queries
 * directly.
 * 
 * Any $rc parameters may be either an EarthIT_Schema_ResourceClass,
 * or the name of a resource class.
 * 
 * Methods that insert new objects may allow primary key fields to be
 * left unspecified if values can be generated.
 *
 * Methods that take an $itemData parameter take that item in 'schema form'.
 * i.e. the one where field names are spelled out in natural language,
 * like 'last name'
 *
 * $filters parameters are an array of filters to be ANDed together.
 * Entries are treated as follows:
 * - Integer-keyed entries must be SQLExpressions and must reference 
 * - The keys of string-keyed entries refer to field names and their values
 *   are interpreted to match those field names.
 *   - When the value is an array, it is interpreted as an IN (values)
 *     matcher, and all values must be scalars to match against exactly
 *   - When the value is a string of the form "<operator>:<value>",
 *     where <operator> is 'eq','ne','lt','le','gt','ge','in',or 'like'
 *     which is interpreted as a FieldMatcher
 *   - When the value is a FieldMatcher, it matches the value of that field
 *   - When the value is NULL, it's interpreted as field value = NULL,
 *     which means no results will be returned.
 *   - When the value is any other scalar, it will be converted to the correct
 *     type for the field being matched, and that value must match exactly.
 *
 * This interface is designed to be used from manually-written code and
 * is NOT intended to do some relatively complex tasks, such as:
 * - Automatically generating complex queries involving joins or returning
 *   nested object structures
 * - Dealing with objects in any other form than 'schema form'
 * For those kind of things you want to use a different system, though
 * there will hopefully be some shared code underneath.
 */
interface TFMPM_StorageHelper
{
	/**
	 * Indicate that you expect to be calling newEntityId $count times.
	 * This may preallocate them (or cause them to be allocated in one
	 * big block when newEntityId is called) to reduce back+forth with
	 * the database.
	 */
	public function preallocateEntityIds($count);
	/**
	 * Return a newly allocated entity ID.
	 */
	public function newEntityId();
	/**
	 * Insert new items.
	 * Use only when you know that there will be no key collisions.
	 * If the item already exists, this will probably result in an error.
	 * Default values will be filled in by the database, but you won't
	 * know what they are because this function returns nothing.
	 */
	public function insertNewItems($rc, array $itemData);
	/**
	 * Insert a single new item.
	 * Suggested implementation is just to call insertNewItems($rc, [$itemData]);
	 * Should error if the item already exists.
	 * Returns nothings.
	 */
	public function insertNewItem($rc, array $itemData);
	/**
	 * Insert a new item or update it if it doesn't already exist.
	 * Returns nothings.
	 */
	public function upsertItem($rc, array $itemData);
	
	/**
	 * Inserts a new item or updates an existing one,
	 * returning the data for the item that's actually
	 * stored in the database.
	 * 
	 * This is the same as upsertItem except that it returns something.
	 * 
	 * @return array|null 
	 */
	public function postItem($rc, array $itemData);
	
	public function getItemById($rc, $itemId);
	/**
	 * Fetch a list of items matching the given filters.
	 *
	 * @param array $filters an array filters (see class documentation)
	 * @param array $orderBy list of fields to order by, optionally prefixed with '+' or '-'
	 */
	public function getItems($rc, array $filters=[], array $orderBy=[], array $withs=[], $skip=0, $limit=null, array $options=array());
	/**
	 * Return the first item returned by getItems($rc, $filters, $orderBy);
	 */
	public function getItem($rc, array $filters=[], array $orderBy=[], array $withs=[], $skip=0, array $options=array());
	/**
	 * Delete all items from the given class matching the given filters.
	 */
	public function deleteItems($rc, array $filters=[]);
}
