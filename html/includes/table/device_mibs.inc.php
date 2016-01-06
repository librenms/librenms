<?php
/*
 * LibreNMS device MIB association browser
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
    'included_by',
    'last_modified',
);

if (isset($_POST['device_id'])) {
    // device_id supplied - get details for a single device
    // used by device MIB page
    $params = array(
        $_POST['device_id'],
    );
    $sql = 'SELECT * FROM `device_mibs`';
    $wheresql = ' WHERE `device_id` = ?';
    $sortcolumns = 3;
    $count_sql = "SELECT COUNT(*) FROM `device_mibs`".$wheresql;
}
else {
    // device_id not supplied - get details for a all devices
    // used by all device MIBs page
    $params = array();
    $sql = 'SELECT `d`.`hostname` as `hostname`, `dm`.* FROM `devices` `d`, `device_mibs` `dm`';
    $wheresql = ' WHERE `d`.`device_id` = `dm`.`device_id`';
    array_unshift($columns, 'hostname');
    $sortcolumns = 4;
    $count_sql = "SELECT COUNT(*) FROM `devices` `d`, `device_mibs` `dm`".$wheresql;
}

// all columns are searchable - search across them
if (isset($searchPhrase) && !empty($searchPhrase)) {
    $searchsql = implode(' OR ', array_map("search_phrase_column", array_map("mres", $columns)));
    $wheresql .= " AND ( $searchsql )";
}
$sql .= $wheresql;

// get total
$total = dbFetchCell($count_sql, $params);
if (empty($total)) {
    $total = 0;
}

// set up default sort
if (!isset($sort) || empty($sort)) {
    $sort = implode(', ', array_map("mres", array_slice($columns, 0, $sortcolumns)));
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
    if (!isset($_POST['device_id'])) {
        $device = device_by_id_cache($mib['device_id']);
        $mibrow['hostname'] = generate_device_link($device,
            $mib['hostname'], array('tab' => 'mib'));
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
