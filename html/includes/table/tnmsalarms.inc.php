<?php

$columns = array(
    'neName',
    'neType',
    'alarm_cause',
    'alarm_location',
    'neAlarmtimestamp',
);

if (isset($vars['device_id'])) {
    $params = array(
        $vars['device_id'],
    );
    $sql = 'select `tnmsneinfo`.`neName`, `tnmsneinfo`.`neType`, `tnms_alarms`.`alarm_cause`, `tnms_alarms`.`alarm_location`, `tnms_alarms`.`neAlarmtimestamp` from `tnms_alarms` inner join `tnmsneinfo` on `tnms_alarms`.`tnmsne_info_id`=`tnmsneinfo`.`tnmsne_info_id`';
    $wheresql = ' WHERE `device_id` = ?';
    $sortcolumns = 3;
    $count_sql = "SELECT COUNT(*) FROM `tnms_alarms`".$wheresql;

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
    foreach (dbFetchRows($sql, $params) as $tnmsalarms) {
        $response[] = array(
            'neName'     => $tnmsalarms['neName'],
            'neType'  => $tnmsalarms['neType'],
            'alarm_cause' => $tnmsalarms['alarm_cause'],
            'alarm_locations' => $tnmsalarms['alarm_location'],
            'neAlarmtimestamp'  => $tnmsalarms['neAlarmtimestamp'],
        );
    }

    $output = array(
        'current'  => $current,
        'rowCount' => $rowCount,
        'rows'     => $response,
        'total'    => $total,
    );
    echo _json_encode($output);
}

