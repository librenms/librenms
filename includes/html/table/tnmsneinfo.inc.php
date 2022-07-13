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

$columns = [
    'neName',
    'neLocation',
    'neType',
    'neOpMode',
    'neAlarm',
    'neOpState',
];

if (isset($vars['device_id'])) {
    $params = [
        $vars['device_id'],
    ];
    $sql = 'SELECT `neName`,`neLocation`,`neType`,`neOpMode`,`neAlarm`,`neOpState` FROM `tnmsneinfo`';
    $wheresql = ' WHERE `device_id` = ?';
    $sortcolumns = 3;
    $count_sql = 'SELECT COUNT(id) FROM `tnmsneinfo`' . $wheresql;

    // all columns are searchable - search across them
    if (isset($searchPhrase) && ! empty($searchPhrase)) {
        $searchsql = implode(' OR ', array_map('search_phrase_column', $columns));
        $wheresql .= " AND ( $searchsql )";
    }
    $sql .= $wheresql;

    // get total
    $total = dbFetchCell($count_sql, $params);
    if (empty($total)) {
        $total = 0;
    }

    // set up default sort
    if (! isset($sort) || empty($sort)) {
        $sort = implode(', ', array_slice($columns, 0, $sortcolumns));
    }
    $sql .= " ORDER BY $sort";

    // select only the required rows
    if (isset($current)) {
        $limit_low = (($current * $rowCount) - ($rowCount));
        $limit_high = $rowCount;
    }
    if ($rowCount != -1) {
        $sql .= " LIMIT $limit_low,$limit_high";
    }

    // load data from database into response array
    $response = [];
    foreach (dbFetchRows($sql, $params) as $tnmsne) {
        if ($tnmsne['neOpMode'] == 'operation') {
            $neop = '<span style="min-width:40px; display:inlink-block;" class="label label-success">operation</span>';
        } else {
            $neop = '<span style="min-width:40px; display:inlink-block;" class="label label-danger">' . $tnmsne['neOpMode'] . '</span>';
        }
        switch ($tnmsne['neAlarm']) {
            case 'cleared':
                $alarm = '<span style="min-width:40px; display:inline-block;" class="label label-success">cleared</span>';
                break;
            case 'warning':
                $alarm = '<span style="min-width:40px; display:inline-block;" class="label label-warning">warning</span>';
                break;
            case 'minor':
            case 'major':
            case 'critical':
            case 'indeterminate':
                $alarm = '<span style="min-width:40px; display:inline-block;" class="label label-danger">' . $tnmsne['neAlarm'] . '</span>';
                break;
            default:
                $alarm = '<span style="min-width:40px; display:inline-block;" class="label label-default">' . $tnmsne['neAlarm'] . '</span>';
        }
        if ($tnmsne['neOpState'] == 'enabled') {
            $opstate = '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">enabled</span></td>';
        } else {
            $opstate = '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-danger">' . $tnmsne['neOpState'] . '</span></td>';
        }
        $response[] = [
            'neName'     => $tnmsne['neName'],
            'neLocation' => $tnmsne['neLocation'],
            'neType'     => $tnmsne['neType'],
            'neOpMode'   => $neop,
            'neAlarm'    => $alarm,
            'neOpState'  => $opstate,
        ];
    }

    $output = [
        'current'  => $current,
        'rowCount' => $rowCount,
        'rows'     => $response,
        'total'    => $total,
    ];
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
