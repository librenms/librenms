<?php

/*
 * dbFacile - A Database API that should have existed from the start
 * Version 0.4.3
 *
 * This code is covered by the MIT license http://en.wikipedia.org/wiki/MIT_License
 *
 * By Alan Szlosek from http://www.greaterscope.net/projects/dbFacile
 *
 * The non-OO version of dbFacile. It's a bit simplistic, but gives you the
 * really useful bits in non-class form.
 *
 * Usage
 * 1. Connect to MySQL as you normally would ... this code uses an existing connection
 * 2. Use dbFacile as you normally would, without the object context
 * 3. Oh, and dbFetchAll() is now dbFetchRows()
 */

use LibreNMS\Config;
use LibreNMS\Exceptions\DatabaseConnectException;

function dbIsConnected()
{
    global $database_link;
    if (empty($database_link)) {
        return false;
    }

    return mysqli_ping($database_link);
}

/**
 * Connect to the database.
 * Will use global $config variables if they are not sent: db_host, db_user, db_pass, db_name, db_port, db_socket
 *
 * @param string $db_host
 * @param string $db_user
 * @param string $db_pass
 * @param string $db_name
 * @param string $db_port
 * @param string $db_socket
 * @return mysqli
 * @throws DatabaseConnectException
 */
function dbConnect($db_host = null, $db_user = '', $db_pass = '', $db_name = '', $db_port = null, $db_socket = null)
{
    global $database_link;

    if (dbIsConnected()) {
        return $database_link;
    }

    if (!function_exists('mysqli_connect')) {
        throw new DatabaseConnectException("mysqli extension not loaded!");
    }

    if (is_null($db_host)) {
        $db_config = Config::getDatabaseSettings();
        extract($db_config);
        /** @var string $db_host */
        /** @var string $db_port */
        /** @var string $db_socket */
        /** @var string $db_name */
        /** @var string $db_user */
        /** @var string $db_pass */
    }

    if (empty($db_socket)) {
        $db_socket = null;
    }
    if (!is_numeric($db_port)) {
        $db_port = null;
    }

    if (!$db_host && !$db_socket) {
        throw new DatabaseConnectException("Database configuration not configured");
    }

    $database_link = @mysqli_connect('p:' . $db_host, $db_user, $db_pass, null, $db_port, $db_socket);
    if ($database_link === false) {
        $error = mysqli_connect_error();
        if ($error == 'No such file or directory') {
            $error = 'Could not connect to ' . $db_host;
        }
        throw new DatabaseConnectException($error);
    }

    mysqli_options($database_link, MYSQLI_OPT_LOCAL_INFILE, false);

    $database_db = mysqli_select_db($database_link, $db_name);
    if (!$database_db) {
        $db_create_sql = "CREATE DATABASE $db_name CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        mysqli_query($database_link, $db_create_sql);
        $database_db = mysqli_select_db($database_link, $db_name);
    }

    if (!$database_db) {
        throw new DatabaseConnectException("Could not select database: $db_name. " . mysqli_error($database_link));
    }

    dbQuery("SET NAMES 'utf8'");
    dbQuery("SET CHARACTER SET 'utf8'");
    dbQuery("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");

    return $database_link;
}

/*
 * Performs a query using the given string.
 * Used by the other _query functions.
 * */


function dbQuery($sql, $parameters = array())
{
    global $fullSql, $debug, $sql_debug, $database_link, $config;
    $fullSql = dbMakeQuery($sql, $parameters);
    if ($debug) {
        if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
            $fullSql = str_replace(PHP_EOL, '', $fullSql);
            if (preg_match('/(INSERT INTO `alert_log`).*(details)/i', $fullSql)) {
                echo "\nINSERT INTO `alert_log` entry masked due to binary data\n";
            } else {
                c_echo('SQL[%y'.$fullSql."%n] \n");
            }
        } else {
            $sql_debug[] = $fullSql;
        }
    }

    $result = mysqli_query($database_link, $fullSql);
    if (!$result) {
        $mysql_error = mysqli_error($database_link);
        if (isset($config['mysql_log_level']) && ((in_array($config['mysql_log_level'], array('INFO', 'ERROR')) && !preg_match('/Duplicate entry/', $mysql_error)) || in_array($config['mysql_log_level'], array('DEBUG')))) {
            if (!empty($mysql_error)) {
                $error_msg =  "MySQL Error: $mysql_error ($fullSql)";
                c_echo("%R$error_msg%n\n", isCli() || $debug);
                logfile(date($config['dateformat']['compact']) . ' ' . $error_msg);
            }
        }
    }

    return $result;
}//end dbQuery()


/*
 * Passed an array and a table name, it attempts to insert the data into the table.
 * Check for boolean false to determine whether insert failed
 * */


function dbInsert($data, $table)
{
    global $database_link;
    $time_start = microtime(true);

    // the following block swaps the parameters if they were given in the wrong order.
    // it allows the method to work for those that would rather it (or expect it to)
    // follow closer with SQL convention:
    // insert into the TABLE this DATA
    if (is_string($data) && is_array($table)) {
        $tmp   = $data;
        $data  = $table;
        $table = $tmp;
        // trigger_error('QDB - Parameters passed to insert() were in reverse order, but it has been allowed', E_USER_NOTICE);
    }

    $sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', array_keys($data)).'`)  VALUES ('.implode(',', dbPlaceHolders($data)).')';

    dbBeginTransaction();
    $result = dbQuery($sql, $data);
    if ($result) {
        $id = mysqli_insert_id($database_link);
        dbCommitTransaction();
        // return $id;
    } else {
        if ($table != 'Contact') {
            trigger_error('QDB - Insert failed.', E_USER_WARNING);
        }

        dbRollbackTransaction();
        $id = null;
    }

    recordDbStatistic('insert', $time_start);
    return $id;
}//end dbInsert()


/*
 * Passed an array and a table name, it attempts to insert the data into the table.
 * $data is an array (rows) of key value pairs.  keys are fields.  Rows need to have same fields.
 * Check for boolean false to determine whether insert failed
 * */


function dbBulkInsert($data, $table)
{
    $time_start = microtime(true);
    // the following block swaps the parameters if they were given in the wrong order.
    // it allows the method to work for those that would rather it (or expect it to)
    // follow closer with SQL convention:
    // insert into the TABLE this DATA
    if (is_string($data) && is_array($table)) {
        $tmp   = $data;
        $data  = $table;
        $table = $tmp;
    }
    // check that data isn't an empty array
    if (empty($data)) {
        return false;
    }
    // make sure we have fields to insert
    $fields = array_keys(reset($data));
    if (empty($fields)) {
        return false;
    }

    $sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $fields).'`)  VALUES ';
    $values ='';

    foreach ($data as $row) {
        if ($values != '') {
            $values .= ',';
        }
        $rowvalues='';
        foreach ($row as $key => $value) {
            if ($rowvalues != '') {
                $rowvalues .= ',';
            }
            if (is_null($value)) {
                $rowvalues .= 'NULL';
            } else {
                $rowvalues .= "'" . mres($value) . "'";
            }
        }
        $values .= "(".$rowvalues.")";
    }

    $result = dbQuery($sql.$values);

    recordDbStatistic('insert', $time_start);
    return $result;
}//end dbBulkInsert()


/*
 * Passed an array, table name, WHERE clause, and placeholder parameters, it attempts to update a record.
 * Returns the number of affected rows
 * */


function dbUpdate($data, $table, $where = null, $parameters = array())
{
    global $fullSql, $database_link;
    $time_start = microtime(true);

    // the following block swaps the parameters if they were given in the wrong order.
    // it allows the method to work for those that would rather it (or expect it to)
    // follow closer with SQL convention:
    // update the TABLE with this DATA
    if (is_string($data) && is_array($table)) {
        $tmp   = $data;
        $data  = $table;
        $table = $tmp;
        // trigger_error('QDB - The first two parameters passed to update() were in reverse order, but it has been allowed', E_USER_NOTICE);
    }

    // need field name and placeholder value
    // but how merge these field placeholders with actual $parameters array for the WHERE clause
    $sql = 'UPDATE `'.$table.'` set ';
    foreach ($data as $key => $value) {
        $sql .= '`'.$key.'` '.'=:'.$key.',';
    }

    $sql = substr($sql, 0, -1);
    // strip off last comma
    if ($where) {
        $sql .= ' WHERE '.$where;
        $data = array_merge($data, $parameters);
    }

    if (dbQuery($sql, $data)) {
        $return = mysqli_affected_rows($database_link);
    } else {
        // echo("$fullSql");
        trigger_error('QDB - Update failed.', E_USER_WARNING);
        $return = false;
    }

    recordDbStatistic('update', $time_start);
    return $return;
}//end dbUpdate()


function dbDelete($table, $where = null, $parameters = array())
{
    global $database_link;
    $time_start = microtime(true);

    $sql = 'DELETE FROM `'.$table.'`';
    if ($where) {
        $sql .= ' WHERE '.$where;
    }

    $result = dbQuery($sql, $parameters);

    recordDbStatistic('delete', $time_start);
    if ($result) {
        return mysqli_affected_rows($database_link);
    } else {
        return false;
    }
}//end dbDelete()


/**
 * Delete orphaned entries from a table that no longer have a parent in parent_table
 * Format of parents array is as follows table.table_key_column<.target_key_column>
 *
 * @param string $target_table The table to delete entries from
 * @param array $parents an array of parent tables to check.
 * @return bool|int
 */
function dbDeleteOrphans($target_table, $parents)
{
    global $database_link;
    $time_start = microtime(true);

    if (empty($parents)) {
        // don't delete all entries if parents is missing
        return false;
    }

    $target_table = mres($target_table);
    $sql = "DELETE T FROM `$target_table` T";
    $where = array();

    foreach ((array)$parents as $parent) {
        $parent_parts = explode('.', mres($parent));
        if (count($parent_parts) == 2) {
            list($parent_table, $parent_column) = $parent_parts;
            $target_column = $parent_column;
        } elseif (count($parent_parts) == 3) {
            list($parent_table, $parent_column, $target_column) = $parent_parts;
        } else {
            // invalid input
            return false;
        }

        $sql .= " LEFT JOIN `$parent_table` ON `$parent_table`.`$parent_column` = T.`$target_column`";
        $where[] = " `$parent_table`.`$parent_column` IS NULL";
    }

    $query = "$sql WHERE" . implode(' AND', $where);
    $result = dbQuery($query, array());

    recordDbStatistic('delete', $time_start);
    if ($result) {
        return mysqli_affected_rows($database_link);
    } else {
        return false;
    }
}

/*
 * Fetches all of the rows (associatively) from the last performed query.
 * Most other retrieval functions build off this
 * */


function dbFetchRows($sql, $parameters = array())
{
    $time_start = microtime(true);
    $result         = dbQuery($sql, $parameters);

    if (mysqli_num_rows($result) > 0) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_free_result($result);

        recordDbStatistic('fetchrows', $time_start);
        return $rows;
    }

    mysqli_free_result($result);

    // no records, thus return empty array
    // which should evaluate to false, and will prevent foreach notices/warnings
    recordDbStatistic('fetchrows', $time_start);
    return array();
}//end dbFetchRows()


/*
 * This is intended to be the method used for large result sets.
 * It is intended to return an iterator, and act upon buffered data.
 * */


function dbFetch($sql, $parameters = array())
{
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
}//end dbFetch()


/*
 * Like fetch(), accepts any number of arguments
 * The first argument is an sprintf-ready query stringTypes
 * */


function dbFetchRow($sql = null, $parameters = array())
{
    $time_start = microtime(true);
    $result         = dbQuery($sql, $parameters);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        recordDbStatistic('fetchrow', $time_start);
        return $row;
    } else {
        return null;
    }
}//end dbFetchRow()


/*
 * Fetches the first call from the first row returned by the query
 * */


function dbFetchCell($sql, $parameters = array())
{
    $time_start = microtime(true);
    $row = dbFetchRow($sql, $parameters);

    recordDbStatistic('fetchcell', $time_start);
    if ($row) {
        return array_shift($row);
        // shift first field off first row
    }
    return null;
}//end dbFetchCell()


/*
 * This method is quite different from fetchCell(), actually
 * It fetches one cell from each row and places all the values in 1 array
 * */


function dbFetchColumn($sql, $parameters = array())
{
    $time_start = microtime(true);
    $cells          = array();
    foreach (dbFetch($sql, $parameters) as $row) {
        $cells[] = array_shift($row);
    }

    recordDbStatistic('fetchcolumn', $time_start);
    return $cells;
}//end dbFetchColumn()


/*
 * Should be passed a query that fetches two fields
 * The first will become the array key
 * The second the key's value
 */


function dbFetchKeyValue($sql, $parameters = array())
{
    $data = array();
    foreach (dbFetch($sql, $parameters) as $row) {
        $key = array_shift($row);
        if (sizeof($row) == 1) {
            // if there were only 2 fields in the result
            // use the second for the value
            $data[$key] = array_shift($row);
        } else {
            // if more than 2 fields were fetched
            // use the array of the rest as the value
            $data[$key] = $row;
        }
    }

    return $data;
}//end dbFetchKeyValue()


/*
 * This combines a query and parameter array into a final query string for execution
 * PDO drivers don't need to use this
 */


function dbMakeQuery($sql, $parameters)
{
    // bypass extra logic if we have no parameters
    if (sizeof($parameters) == 0) {
        return $sql;
    }

    $parameters = dbPrepareData($parameters);
    // separate the two types of parameters for easier handling
    $questionParams = array();
    $namedParams    = array();
    foreach ($parameters as $key => $value) {
        if (is_numeric($key)) {
            $questionParams[] = $value;
        } else {
            $namedParams[':'.$key] = $value;
        }
    }

    // sort namedParams in reverse to stop substring squashing
    krsort($namedParams);

    // split on question-mark and named placeholders
    if (preg_match('/(\[\[:[\w]+:\]\])/', $sql)) {
        $result = preg_split('/(\?[a-zA-Z0-9_-]*)/', $sql, -1, (PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));
    } else {
        $result = preg_split('/(\?|:[a-zA-Z0-9_-]+)/', $sql, -1, (PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));
    }

    // every-other item in $result will be the placeholder that was found
    $query            = '';
    $res_size = sizeof($result);
    for ($i = 0; $i < $res_size; $i += 2) {
        $query .= $result[$i];

        $j = ($i + 1);
        if (array_key_exists($j, $result)) {
            $test = $result[$j];
            if ($test == '?') {
                $query .= array_shift($questionParams);
            } else {
                $query .= $namedParams[$test];
            }
        }
    }

    return $query;
}//end dbMakeQuery()


function dbPrepareData($data)
{
    global $database_link;
    $values = array();

    foreach ($data as $key => $value) {
        $escape = true;
        // don't quote or esc if value is an array, we treat it
        // as a "decorator" that tells us not to escape the
        // value contained in the array
        if (is_array($value) && !is_object($value)) {
            $escape = false;
            $value  = array_shift($value);
        }

        // it's not right to worry about invalid fields in this method because we may be operating on fields
        // that are aliases, or part of other tables through joins
        // if(!in_array($key, $columns)) // skip invalid fields
        // continue;
        if ($escape) {
            $values[$key] = "'".mysqli_real_escape_string($database_link, $value)."'";
        } else {
            $values[$key] = $value;
        }
    }

    return $values;
}//end dbPrepareData()

/**
 * Given a data array, this returns an array of placeholders
 * These may be question marks, or ":email" type
 *
 * @param array $values
 * @return array
 */
function dbPlaceHolders($values)
{
    $data = array();
    foreach ($values as $key => $value) {
        if (is_numeric($key)) {
            $data[] = '?';
        } else {
            $data[] = ':'.$key;
        }
    }

    return $data;
}//end dbPlaceHolders()


function dbBeginTransaction()
{
    global $database_link;
    mysqli_query($database_link, 'begin');
}//end dbBeginTransaction()


function dbCommitTransaction()
{
    global $database_link;
    mysqli_query($database_link, 'commit');
}//end dbCommitTransaction()


function dbRollbackTransaction()
{
    global $database_link;
    mysqli_query($database_link, 'rollback');
}//end dbRollbackTransaction()

/**
 * Generate a string of placeholders to pass to fill in a list
 * result will look like this: (?, ?, ?, ?)
 *
 * @param $count
 * @return string placholder list
 */
function dbGenPlaceholders($count)
{
    return '(' . implode(',', array_fill(0, $count, '?')) . ')';
}

/**
 * Update statistics for db operations
 *
 * @param string $stat fetchcell, fetchrow, fetchrows, fetchcolumn, update, insert, delete
 * @param float $start_time The time the operation started with 'microtime(true)'
 * @return float  The calculated run time
 */
function recordDbStatistic($stat, $start_time)
{
    global $db_stats;

    if (!isset($db_stats)) {
        $db_stats = array(
            'ops' => array(
                'insert' => 0,
                'update' => 0,
                'delete' => 0,
                'fetchcell' => 0,
                'fetchcolumn' => 0,
                'fetchrow' => 0,
                'fetchrows' => 0,
            ),
            'time' => array(
                'insert' => 0.0,
                'update' => 0.0,
                'delete' => 0.0,
                'fetchcell' => 0.0,
                'fetchcolumn' => 0.0,
                'fetchrow' => 0.0,
                'fetchrows' => 0.0,
            ),
        );
    }

    $runtime = microtime(true) - $start_time;
    $db_stats['ops'][$stat]++;
    $db_stats['time'][$stat] += $runtime;

    //double accounting corrections
    if ($stat == 'fetchcolumn') {
        $db_stats['ops']['fetchrows']--;
        $db_stats['time']['fetchrows'] -= $runtime;
    }
    if ($stat == 'fetchcell') {
        $db_stats['ops']['fetchrow']--;
        $db_stats['time']['fetchrow'] -= $runtime;
    }

    return $runtime;
}

/**
 * Synchronize a relationship to a list of related ids
 *
 * @param string $table
 * @param string $target_column column name for the target
 * @param int $target column target id
 * @param string $list_column related column names
 * @param array $list list of related ids
 * @return array [$inserted, $deleted]
 */
function dbSyncRelationship($table, $target_column = null, $target = null, $list_column = null, $list = null)
{
    $inserted = 0;

    $delete_query = "`$target_column`=? AND `$list_column`";
    $delete_params = [$target];
    if (!empty($list)) {
        $delete_query .= ' NOT IN ' . dbGenPlaceholders(count($list));
        $delete_params = array_merge($delete_params, $list);
    }
    $deleted = (int)dbDelete($table, $delete_query, $delete_params);

    $db_list = dbFetchColumn("SELECT `$list_column` FROM `$table` WHERE `$target_column`=?", [$target]);
    foreach ($list as $item) {
        if (!in_array($item, $db_list)) {
            dbInsert([$target_column => $target, $list_column => $item], $table);
            $inserted++;
        }
    }

    return [$inserted, $deleted];
}

/**
 * Synchronize a relationship to a list of relations
 *
 * @param string $table
 * @param array $relationships array of relationship pairs with columns as keys and ids as values
 * @return array [$inserted, $deleted]
 */
function dbSyncRelationships($table, $relationships = array())
{
    $changed = [[0, 0]];
    list($target_column, $list_column) = array_keys(reset($relationships));

    $grouped = [];
    foreach ($relationships as $relationship) {
        $grouped[$relationship[$target_column]][] = $relationship[$list_column];
    }

    foreach ($grouped as $target => $list) {
        $changed[] = dbSyncRelationship($table, $target_column, $target, $list_column, $list);
    }

    return [array_sum(array_column($changed, 0)), array_sum(array_column($changed, 1))];
}
