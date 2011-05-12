<?php
/*
dbFacile - A Database API that should have existed from the start
Version 0.4.3

This code is covered by the MIT license http://en.wikipedia.org/wiki/MIT_License

By Alan Szlosek from http://www.greaterscope.net/projects/dbFacile

The non-OO version of dbFacile. It's a bit simplistic, but gives you the 
really useful bits in non-class form.

Usage
 1. Connect to MySQL as you normally would ... this code uses an existing connection
 2. Use dbFacile as you normally would, without the object context
 3. Oh, and dbFetchAll() is now dbFetchRows()

*/

/*
 * Performs a query using the given string.
 * Used by the other _query functions.
 * */
function dbQuery($sql, $parameters = array()) {
	global $fullSql;
	$fullSql = dbMakeQuery($sql, $parameters);
	/*
	if($this->logFile)
		$time_start = microtime(true);
	*/

	$result = mysql_query($fullSql); // sets $this->result
	/*
	if($this->logFile) {
		$time_end = microtime(true);
		fwrite($this->logFile, date('Y-m-d H:i:s') . "\n" . $fullSql . "\n" . number_format($time_end - $time_start, 8) . " seconds\n\n");
	}
	*/

	if($result === false && (error_reporting() & 1)) {
		// aye. this gets triggers on duplicate Contact insert
		//trigger_error('QDB - Error in query: ' . $fullSql . ' : ' . mysql_error(), E_USER_WARNING);
	}
	return $result;
}

/*
 * Passed an array and a table name, it attempts to insert the data into the table.
 * Check for boolean false to determine whether insert failed
 * */
function dbInsert($data, $table) {
	global $fullSql;
	// the following block swaps the parameters if they were given in the wrong order.
	// it allows the method to work for those that would rather it (or expect it to)
	// follow closer with SQL convention:
	// insert into the TABLE this DATA
	if(is_string($data) && is_array($table)) {
		$tmp = $data;
		$data = $table;
		$table = $tmp;
		//trigger_error('QDB - Parameters passed to insert() were in reverse order, but it has been allowed', E_USER_NOTICE);
	}

	$sql = 'insert into ' . $table . ' (' . implode(',', array_keys($data)) . ') values(' . implode(',', dbPlaceHolders($data)) . ')';

	dbBeginTransaction();
	$result = dbQuery($sql, $data);
	if($result) {
		$id = mysql_insert_id();
		dbCommitTransaction();
		return $id;
	} else {
		if($table != 'Contact') {
			trigger_error('QDB - Insert failed.', E_WARNING);
		}
		dbRollbackTransaction();
		return false;
	}
}

/*
 * Passed an array, table name, where clause, and placeholder parameters, it attempts to update a record.
 * Returns the number of affected rows
 * */
function dbUpdate($data, $table, $where = null, $parameters = array()) {
	global $fullSql;
	// the following block swaps the parameters if they were given in the wrong order.
	// it allows the method to work for those that would rather it (or expect it to)
	// follow closer with SQL convention:
	// update the TABLE with this DATA
	if(is_string($data) && is_array($table)) {
		$tmp = $data;
		$data = $table;
		$table = $tmp;
		//trigger_error('QDB - The first two parameters passed to update() were in reverse order, but it has been allowed', E_USER_NOTICE);
	}

	// need field name and placeholder value
	// but how merge these field placeholders with actual $parameters array for the where clause
	$sql = 'update ' . $table . ' set ';
	foreach($data as $key => $value) {
		$sql .= $key . '=:' . $key . ',';
	}
	$sql = substr($sql, 0, -1); // strip off last comma

	if($where) {
		$sql .= ' where ' . $where;
		$data = array_merge($data, $parameters);
	}

	if(dbQuery($sql, $data)) {
		return mysql_affected_rows();
	} else {
		trigger_error('QDB - Update failed.', E_WARNING);
		return false;
	}
}

function dbDelete($table, $where = null, $parameters = array()) {
	$sql = 'delete from ' . $table;
	if($where) {
		$sql .= ' where ' . $where;
	}
	if(dbQuery($sql, $parameters)) {
		return mysql_affected_rows();
	} else {
		return false;
	}
}

/*
 * Fetches all of the rows (associatively) from the last performed query.
 * Most other retrieval functions build off this
 * */
function dbFetchRows($sql, $parameters = array()) {
	$result = dbQuery($sql, $parameters);
	if(mysql_num_rows($result) > 0) {
		$rows = array();
		while($row = mysql_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysql_free_result($result);
		return $rows;
	}
	mysql_free_result($result);
	// no records, thus return empty array
	// which should evaluate to false, and will prevent foreach notices/warnings 
	return array();
}
/*
 * This is intended to be the method used for large result sets.
 * It is intended to return an iterator, and act upon buffered data.
 * */
function dbFetch($sql, $parameters = array()) {
	return dbFetchRows($sql, $parameters);
	/*
	// for now, don't do the iterator thing
	$result = dbQuery($sql, $parameters);
	if($result) {
		// return new iterator
		return new dbIterator($result);
	} else {
		return null; // ??
	}
	*/
}

/*
 * Like fetch(), accepts any number of arguments
 * The first argument is an sprintf-ready query stringTypes
 * */
function dbFetchRow($sql = null, $parameters = array()) {
	$result = dbQuery($sql, $parameters);
	if($result) {
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $row;
	} else {
		return null;
	}
}

/*
 * Fetches the first call from the first row returned by the query
 * */
function dbFetchCell($sql, $parameters = array()) {
	$row = dbFetchRow($sql, $parameters);
	if($row) {
		return array_shift($row); // shift first field off first row
	}
	return null;
}

/*
 * This method is quite different from fetchCell(), actually
 * It fetches one cell from each row and places all the values in 1 array
 * */
function dbFetchColumn($sql, $parameters = array()) {
	$cells = array();
	foreach(dbFetch($sql, $parameters) as $row) {
		$cells[] = array_shift($row);
	}
	return $cells;
}

/*
 * Should be passed a query that fetches two fields
 * The first will become the array key
 * The second the key's value
 */
function dbFetchKeyValue($sql, $parameters = array()) {
	$data = array();
	foreach(dbFetch($sql, $parameters) as $row) {
		$key = array_shift($row);
		if(sizeof($row) == 1) { // if there were only 2 fields in the result
			// use the second for the value
			$data[ $key ] = array_shift($row);
		} else { // if more than 2 fields were fetched
			// use the array of the rest as the value
			$data[ $key ] = $row;
		}
	}
	return $data;
}

/*
 * This combines a query and parameter array into a final query string for execution
 * PDO drivers don't need to use this
 */
function dbMakeQuery($sql, $parameters) {
	// bypass extra logic if we have no parameters
	if(sizeof($parameters) == 0)
		return $sql;
	$parts = explode('?', $sql);
	$query = array_shift($parts); // put on first part

	$parameters = dbPrepareData($parameters);
	$newParams = array();
	// replace question marks first
	foreach($parameters as $key => $value) {
		if(is_numeric($key)) {
			$query .= $value . array_shift($parts);
			//$newParams[ $key ] = $value;
		} else {
			$newParams[ ':' . $key ] = $value;
		}
	}
	// now replace name place-holders
	// replace place-holders with quoted, escaped values
	/*
	var_dump($query);
	var_dump($newParams);exit;
	*/

	// sort newParams in reverse to stop substring squashing
	krsort($newParams);
	$query = str_replace( array_keys($newParams), $newParams, $query);
	//die($query);
	return $query;
}



/*
 * This should be protected and overloadable by driver classes
 */
function dbPrepareData($data) {
	$values = array();

	foreach($data as $key=>$value) {
		$escape = true;
		// new way to determine whether to quote and escape
		// if value is an array, we treat it as a "decorator" that tells us not to escape the
		// value contained in the array
		if(is_array($value) && !is_object($value)) {
			$escape = false;
			$value = array_shift($value);
		}
		// it's not right to worry about invalid fields in this method because we may be operating on fields
		// that are aliases, or part of other tables through joins 
		//if(!in_array($key, $columns)) // skip invalid fields
		//	continue;
		if($escape)
			$values[$key] = "'" . mysql_real_escape_string($value) . "'";
		else
			$values[$key] = $value;
	}
	return $values;
}

/*
 * Given a data array, this returns an array of placeholders
 * These may be question marks, or ":email" type
 */
function dbPlaceHolders($values) {
	$data = array();
	foreach($values as $key => $value) {
		if(is_numeric($key))
			$data[] = '?';
		else
			$data[] = ':' . $key;
	}
	return $data;
}

function dbBeginTransaction() {
	mysql_query('begin');
}

function dbCommitTransaction() {
	mysql_query('commit');
}

function dbRollbackTransaction() {
	mysql_query('rollback');
}



/*
class dbIterator implements Iterator {
	private $result;
	private $i;
	
	public function __construct($r) {
		$this->result = $r;
		$this->i = 0;
	}
	public function rewind() {
		mysql_data_seek($this->result, 0);
		$this->i = 0;
	}
	public function current() {
		$a = mysql_fetch_assoc($this->result);
		return $a;
	}
	public function key() {
		return $this->i;
	}
	public function next() {
		$this->i++;
		$a = mysql_data_seek($this->result, $this->i);
		if($a === false) {
			$this->i = 0;
		}
		return $a;
	}
	public function valid() {
		return ($this->current() !== false);
	}
}
*/

?>
