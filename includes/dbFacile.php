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
        if ((in_array($config['mysql_log_level'], array('INFO', 'ERROR')) && !preg_match('/Duplicate entry/', $mysql_error)) || (in_array($config['mysql_log_level'], array('DEBUG')))) {
            if (!empty($mysql_error)) {
                logfile(date($config['dateformat']['compact']) . " MySQL Error: $mysql_error ($fullSql)");
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
    if (count($data) === 0) {
        return false;
    }
    if (count($data[0]) === 0) {
        return false;
    }

    $sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', array_keys($data[0])).'`)  VALUES ';
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
            $rowvalues .= "'".mres($value)."'";
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


/*
 * Fetches all of the rows (associatively) from the last performed query.
 * Most other retrieval functions build off this
 * */


function dbFetchRows($sql, $parameters = array(), $nocache = false)
{
    global $config;

    if ($config['memcached']['enable'] && $nocache === false) {
        $result = $config['memcached']['resource']->get(hash('sha512', $sql.'|'.serialize($parameters)));
        if (!empty($result)) {
            return $result;
        }
    }

    $time_start = microtime(true);
    $result         = dbQuery($sql, $parameters);

    if (mysqli_num_rows($result) > 0) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_free_result($result);
        if ($config['memcached']['enable'] && $nocache === false) {
            $config['memcached']['resource']->set(hash('sha512', $sql.'|'.serialize($parameters)), $rows, $config['memcached']['ttl']);
        }
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


function dbFetch($sql, $parameters = array(), $nocache = false)
{
    return dbFetchRows($sql, $parameters, $nocache);
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


function dbFetchRow($sql = null, $parameters = array(), $nocache = false)
{
    global $config;

    if ($config['memcached']['enable'] && $nocache === false) {
        $result = $config['memcached']['resource']->get(hash('sha512', $sql.'|'.serialize($parameters)));
        if (!empty($result)) {
            return $result;
        }
    }

    $time_start = microtime(true);
    $result         = dbQuery($sql, $parameters);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        recordDbStatistic('fetchrow', $time_start);

        if ($config['memcached']['enable'] && $nocache === false) {
            $config['memcached']['resource']->set(hash('sha512', $sql.'|'.serialize($parameters)), $row, $config['memcached']['ttl']);
        }
        return $row;
    } else {
        return null;
    }
}//end dbFetchRow()


/*
 * Fetches the first call from the first row returned by the query
 * */


function dbFetchCell($sql, $parameters = array(), $nocache = false)
{
    $time_start = microtime(true);
    $row = dbFetchRow($sql, $parameters, $nocache);

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


function dbFetchColumn($sql, $parameters = array(), $nocache = false)
{
    $time_start = microtime(true);
    $cells          = array();
    foreach (dbFetch($sql, $parameters, $nocache) as $row) {
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


function dbFetchKeyValue($sql, $parameters = array(), $nocache = false)
{
    $data = array();
    foreach (dbFetch($sql, $parameters, $nocache) as $row) {
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
