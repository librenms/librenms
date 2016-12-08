<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$where = 1;

if ($_SESSION['userlevel'] >= '5') {
    $sql = " FROM `alert_schedule` AS S WHERE $where";
} else {
    $sql     = " FROM `alert_schedule` AS S WHERE $where";
    $param[] = $_SESSION['user_id'];
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`S`.`title` LIKE '%$searchPhrase%' OR `S`.`start` LIKE '%$searchPhrase%' OR `S`.`end` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`S`.`start` DESC ';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `S`.`schedule_id`, DATE_FORMAT(NOW(), '".$config['dateformat']['mysql']['compact']."') AS now, DATE_FORMAT(`S`.`start`, '".$config['dateformat']['mysql']['compact']."') AS `start`, DATE_FORMAT(`S`.`end`, '".$config['dateformat']['mysql']['compact']."') AS `end`, `S`.`title` $sql";

foreach (dbFetchRows($sql, $param) as $schedule) {
    $status = 0;
    $start  = strtotime($schedule['start']);
    $end    = strtotime($schedule['end']);
    $now    = strtotime($schedule['now']);
    if ($end < $now) {
        $status = 1;
    }

    if ($now >= $start && $now < $end) {
        $status = 2;
    }

    $response[] = array(
        'title'  => $schedule['title'],
        'start'  => $schedule['start'],
        'end'    => $schedule['end'],
        'id'     => $schedule['schedule_id'],
        'status' => $status,
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
