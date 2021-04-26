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

use Illuminate\Database\QueryException;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Exceptions\DatabaseConnectException;
use LibreNMS\Util\Laravel;

function dbIsConnected()
{
    return Eloquent::isConnected();
}

/**
 * Connect to the database.
 * Will use global config variables if they are not sent: db_host, db_user, db_pass, db_name, db_port, db_socket
 *
 * @param string $db_host
 * @param string $db_user
 * @param string $db_pass
 * @param string $db_name
 * @param string $db_port
 * @param string $db_socket
 * @return \Illuminate\Database\Connection
 * @throws DatabaseConnectException
 */
function dbConnect($db_host = null, $db_user = '', $db_pass = '', $db_name = '', $db_port = null, $db_socket = null)
{
    if (Eloquent::isConnected()) {
        return Eloquent::DB();
    }

    if (! extension_loaded('pdo_mysql')) {
        throw new DatabaseConnectException('PHP pdo_mysql extension not loaded!');
    }

    try {
        if (! is_null($db_host) || ! empty($db_name)) {
            // legacy connection override
            \Config::set('database.connections.setup', [
                'driver' => 'mysql',
                'host' => $db_host,
                'port' => $db_port,
                'database' => $db_name,
                'username' => $db_user,
                'password' => $db_pass,
                'unix_socket' => $db_socket,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);
            \Config::set('database.default', 'setup');
        }

        Eloquent::boot();
    } catch (PDOException $e) {
        throw new DatabaseConnectException($e->getMessage(), $e->getCode(), $e);
    }

    return Eloquent::DB();
}

/**
 * Performs a query using the given string.
 * @param string $sql
 * @param array $parameters
 * @return bool if query was successful or not
 */
function dbQuery($sql, $parameters = [])
{
    try {
        if (empty($parameters)) {
            // don't use prepared statements for queries without parameters
            return Eloquent::DB()->getPdo()->exec($sql) !== false;
        }

        return Eloquent::DB()->statement($sql, (array) $parameters);
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));

        return false;
    }
}

/**
 * @param array $data
 * @param string $table
 * @return null|int
 */
function dbInsert($data, $table)
{
    $time_start = microtime(true);

    $sql = 'INSERT IGNORE INTO `' . $table . '` (`' . implode('`,`', array_keys($data)) . '`)  VALUES (' . implode(',', dbPlaceHolders($data)) . ')';

    try {
        $result = Eloquent::DB()->insert($sql, (array) $data);
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $data, $pdoe));
    }

    recordDbStatistic('insert', $time_start);
    if ($result) {
        return Eloquent::DB()->getPdo()->lastInsertId();
    } else {
        return null;
    }
}//end dbInsert()

/**
 * Passed an array and a table name, it attempts to insert the data into the table.
 * $data is an array (rows) of key value pairs.  keys are fields.  Rows need to have same fields.
 * Check for boolean false to determine whether insert failed
 *
 * @param array $data
 * @param string $table
 * @return bool
 */
function dbBulkInsert($data, $table)
{
    $time_start = microtime(true);

    // check that data isn't an empty array
    if (empty($data)) {
        return false;
    }

    // make sure we have fields to insert
    $fields = array_keys(reset($data));
    if (empty($fields)) {
        return false;
    }

    // Break into managable chunks to prevent situations where insert
    // fails due to prepared statement having too many placeholders.
    $data_chunks = array_chunk($data, 10000, true);

    foreach ($data_chunks as $data_chunk) {
        try {
            $result = Eloquent::DB()->table($table)->insert((array) $data_chunk);

            recordDbStatistic('insert', $time_start);

            return $result;
        } catch (PDOException $pdoe) {
            // FIXME query?
            dbHandleException(new QueryException("Bulk insert $table", $data_chunk, $pdoe));
        }
    }

    return false;
}//end dbBulkInsert()

/**
 * Passed an array, table name, WHERE clause, and placeholder parameters, it attempts to update a record.
 * Returns the number of affected rows
 *
 * @param array $data
 * @param string $table
 * @param string $where
 * @param array $parameters
 * @return bool|int
 */
function dbUpdate($data, $table, $where = null, $parameters = [])
{
    $time_start = microtime(true);

    // need field name and placeholder value
    // but how merge these field placeholders with actual $parameters array for the WHERE clause
    $sql = 'UPDATE `' . $table . '` set ';
    foreach ($data as $key => $value) {
        $sql .= '`' . $key . '`=';
        if (is_array($value)) {
            $sql .= reset($value);
            unset($data[$key]);
        } else {
            $sql .= '?';
        }
        $sql .= ',';
    }

    // strip keys
    $data = array_values($data);

    $sql = substr($sql, 0, -1);
    // strip off last comma
    if ($where) {
        $sql .= ' WHERE ' . $where;
        $data = array_merge($data, $parameters);
    }

    try {
        $result = Eloquent::DB()->update($sql, (array) $data);

        recordDbStatistic('update', $time_start);

        return $result;
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $data, $pdoe));
    }

    return false;
}//end dbUpdate()

function dbDelete($table, $where = null, $parameters = [])
{
    $time_start = microtime(true);

    $sql = 'DELETE FROM `' . $table . '`';
    if ($where) {
        $sql .= ' WHERE ' . $where;
    }

    try {
        $result = Eloquent::DB()->delete($sql, (array) $parameters);
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));
    }

    recordDbStatistic('delete', $time_start);

    return $result;
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
    $time_start = microtime(true);

    if (empty($parents)) {
        // don't delete all entries if parents is missing
        return false;
    }

    $target_table = $target_table;
    $sql = "DELETE T FROM `$target_table` T";
    $where = [];

    foreach ((array) $parents as $parent) {
        $parent_parts = explode('.', $parent);
        if (count($parent_parts) == 2) {
            [$parent_table, $parent_column] = $parent_parts;
            $target_column = $parent_column;
        } elseif (count($parent_parts) == 3) {
            [$parent_table, $parent_column, $target_column] = $parent_parts;
        } else {
            // invalid input
            return false;
        }

        $sql .= " LEFT JOIN `$parent_table` ON `$parent_table`.`$parent_column` = T.`$target_column`";
        $where[] = " `$parent_table`.`$parent_column` IS NULL";
    }

    $query = "$sql WHERE" . implode(' AND', $where);

    try {
        $result = Eloquent::DB()->delete($query);
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($query, [], $pdoe));
    }

    recordDbStatistic('delete', $time_start);

    return $result;
}

/*
 * Fetches all of the rows (associatively) from the last performed query.
 * Most other retrieval functions build off this
 * */

function dbFetchRows($sql, $parameters = [])
{
    global $PDO_FETCH_ASSOC;
    $time_start = microtime(true);

    try {
        $PDO_FETCH_ASSOC = true;
        $rows = Eloquent::DB()->select($sql, (array) $parameters);

        recordDbStatistic('fetchrows', $time_start);

        return $rows;
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));
    } finally {
        $PDO_FETCH_ASSOC = false;
    }

    return [];
}//end dbFetchRows()

/*
 * This is intended to be the method used for large result sets.
 * It is intended to return an iterator, and act upon buffered data.
 * */

function dbFetch($sql, $parameters = [])
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

function dbFetchRow($sql = null, $parameters = [])
{
    global $PDO_FETCH_ASSOC;
    $time_start = microtime(true);

    try {
        $PDO_FETCH_ASSOC = true;
        $row = Eloquent::DB()->selectOne($sql, (array) $parameters);

        recordDbStatistic('fetchrow', $time_start);

        return $row;
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));
    } finally {
        $PDO_FETCH_ASSOC = false;
    }

    return [];
}//end dbFetchRow()

/*
 * Fetches the first call from the first row returned by the query
 * */

function dbFetchCell($sql, $parameters = [])
{
    global $PDO_FETCH_ASSOC;
    $time_start = microtime(true);

    try {
        $PDO_FETCH_ASSOC = true;
        $row = Eloquent::DB()->selectOne($sql, (array) $parameters);
        recordDbStatistic('fetchcell', $time_start);
        if ($row) {
            return reset($row);
            // shift first field off first row
        }
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));
    } finally {
        $PDO_FETCH_ASSOC = false;
    }

    return null;
}//end dbFetchCell()

/*
 * This method is quite different from fetchCell(), actually
 * It fetches one cell from each row and places all the values in 1 array
 * */

function dbFetchColumn($sql, $parameters = [])
{
    global $PDO_FETCH_ASSOC;
    $time_start = microtime(true);

    $cells = [];

    try {
        $PDO_FETCH_ASSOC = true;
        foreach (Eloquent::DB()->select($sql, (array) $parameters) as $row) {
            $cells[] = reset($row);
        }
        $PDO_FETCH_ASSOC = false;

        recordDbStatistic('fetchcolumn', $time_start);

        return $cells;
    } catch (PDOException $pdoe) {
        dbHandleException(new QueryException($sql, $parameters, $pdoe));
    } finally {
        $PDO_FETCH_ASSOC = false;
    }

    return [];
}//end dbFetchColumn()

/*
 * Should be passed a query that fetches two fields
 * The first will become the array key
 * The second the key's value
 */

function dbFetchKeyValue($sql, $parameters = [])
{
    $data = [];
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

/**
 * Legacy dbFacile indicates DB::raw() as a value wrapped in an array
 *
 * @param array $data
 * @return array
 */
function dbArrayToRaw($data)
{
    array_walk($data, function (&$item) {
        if (is_array($item)) {
            $item = Eloquent::DB()->raw(reset($item));
        }
    });

    return $data;
}

function dbHandleException(QueryException $exception)
{
    $message = $exception->getMessage();

    if ($exception->getCode() == 2002) {
        $message = 'Could not connect to database! ' . $message;
    }

    // ? bindings should already be replaced, just replace named bindings
    foreach ($exception->getBindings() as $key => $value) {
        if (is_string($key)) {
            $message = str_replace(":$key", $value, $message);
        }
    }

    $message .= $exception->getTraceAsString();

    if (Laravel::isBooted()) {
        Log::error($message);
    } else {
        c_echo('%rSQL Error!%n ');
        echo $message . PHP_EOL;
    }

    // TODO remove this
//    exit;
}

/**
 * Given a data array, this returns an array of placeholders
 * These may be question marks, or ":email" type
 *
 * @param array $values
 * @return array
 */
function dbPlaceHolders(&$values)
{
    $data = [];
    foreach ($values as $key => $value) {
        if (is_array($value)) {
            // array wrapped values are raw sql
            $data[] = reset($value);
            unset($values[$key]);
        } elseif (is_numeric($key)) {
            $data[] = '?';
        } else {
            $data[] = ':' . $key;
        }
    }

    return $data;
}//end dbPlaceHolders()

function dbBeginTransaction()
{
    Eloquent::DB()->beginTransaction();
}//end dbBeginTransaction()

function dbCommitTransaction()
{
    Eloquent::DB()->commit();
}//end dbCommitTransaction()

function dbRollbackTransaction()
{
    Eloquent::DB()->rollBack();
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
    global $db_stats, $db_stats_last;

    if (! isset($db_stats)) {
        $db_stats = [
            'ops' => [
                'insert' => 0,
                'update' => 0,
                'delete' => 0,
                'fetchcell' => 0,
                'fetchcolumn' => 0,
                'fetchrow' => 0,
                'fetchrows' => 0,
            ],
            'time' => [
                'insert' => 0.0,
                'update' => 0.0,
                'delete' => 0.0,
                'fetchcell' => 0.0,
                'fetchcolumn' => 0.0,
                'fetchrow' => 0.0,
                'fetchrows' => 0.0,
            ],
        ];
        $db_stats_last = $db_stats;
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
    if (! empty($list)) {
        $delete_query .= ' NOT IN ' . dbGenPlaceholders(count($list));
        $delete_params = array_merge($delete_params, $list);
    }
    $deleted = (int) dbDelete($table, $delete_query, $delete_params);

    $db_list = dbFetchColumn("SELECT `$list_column` FROM `$table` WHERE `$target_column`=?", [$target]);
    foreach ($list as $item) {
        if (! in_array($item, $db_list)) {
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
function dbSyncRelationships($table, $relationships = [])
{
    $changed = [[0, 0]];
    [$target_column, $list_column] = array_keys(reset($relationships));

    $grouped = [];
    foreach ($relationships as $relationship) {
        $grouped[$relationship[$target_column]][] = $relationship[$list_column];
    }

    foreach ($grouped as $target => $list) {
        $changed[] = dbSyncRelationship($table, $target_column, $target, $list_column, $list);
    }

    return [array_sum(array_column($changed, 0)), array_sum(array_column($changed, 1))];
}
