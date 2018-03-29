<?php
/*
 * LibreNMS device MIB OID browser
 *
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * by Paul Gear
 *    based on code by Søren Friis Rosiak <sorenrosiak@gmail.com>
 *    in commit 054bf3ae209f34a2c3bc8968300722004903df1b
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$columns = array(
    'module',
    'mib',
    'object_type',
    'oid',
    'value',
    'numvalue',
    'last_modified',
);


$params = array(
    $vars['device_id'],
);

// start of sql definition
$sql = 'SELECT * FROM `device_oids`';

$wheresql = ' WHERE `device_id` = ?';

// all columns are searchable - search across them
if (isset($searchPhrase) && !empty($searchPhrase)) {
    $searchsql = implode(' OR ', array_map("search_phrase_column", array_map("mres", $columns)));
    $wheresql .= " AND ( $searchsql )";
}
$sql .= $wheresql;

// get total
$count_sql = "SELECT COUNT(*) FROM `device_oids`".$wheresql;
$total     = dbFetchCell($count_sql, $params);
if (empty($total)) {
    $total = 0;
}

// sort by first three columns by default
if (!isset($sort) || empty($sort)) {
    $sort = implode(', ', array_map("mres", array_slice($columns, 0, 3)));
}
$sql .= " ORDER BY $sort";

// select only the required rows
if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}
if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

// load data from database into response array
$response = array();
foreach (dbFetchRows($sql, $params) as $mib) {
    $mibrow = array();
    foreach ($columns as $col) {
        $mibrow[$col] = $mib[$col];
    }
    $response[] = $mibrow;
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
