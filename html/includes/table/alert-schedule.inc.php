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
    $sql = " FROM `alert_schedule` AS S LEFT JOIN `devices` AS `D` ON `S`.`device_id`=`D`.`device_id` WHERE $where";
} else {
    $sql = " FROM `alert_schedule` AS S, devices_perms AS P LEFT JOIN `devices` AS `D` WHERE $where AND `S`.`device_id` = `P`.`device_id` AND `P`.`user_id` = ?";
    $param[] = $_SESSION['user_id'];
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `S`.`start` LIKE '%$searchPhrase%' OR `S`.`end` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`id`) $sql";
$total = dbFetchCell($count_sql,$param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname` ASC ';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT DATE_FORMAT(`S`.`start`, '%D %b %Y %T') AS `start`, DATE_FORMAT(`S`.`end`, '%D %b %Y %T') AS `end`, `D`.`hostname`, `S`.`device_id` $sql";

foreach (dbFetchRows($sql,$param) as $schedule) {
    if ($schedule['device_id'] == '-1') {
        $host_link = 'All devices';
    } else {
        $dev = device_by_id_cache($schedule['device_id']);
        $host_link = generate_device_link($dev, shorthost($dev['hostname']));
    }
    $response[] = array('hostname'=>$host_link,
                        'start'=>$schedule['start'],
                        'end'=>$schedule['end']);
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
