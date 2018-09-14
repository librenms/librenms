<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2018 TheGreatDoc
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$device_id = $vars['device_id'];

$sql = " FROM `bgpPeers` AS `B` LEFT JOIN `devices` AS `D` ON `B`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=?";
$param[] = $device_id;

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `B`.`bgpPeerRemoteAs` LIKE '%$searchPhrase%' OR `B`.`bgpPeerIdentifier` LIKE '%$searchPhrase%' OR `B`.`bgpPeerDescr` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`bgpPeer_id`) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `B`.`bgpPeerRemoteAs`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

foreach (dbFetchRows($sql, $param) as $routing) {
    $response[] = array(
        'routing_id' => $routing['bgpPeer_id'],
        'hostname' => generate_device_link($routing),
        'bgpPeerIdentifier' => $routing['bgpPeerIdentifier'],
        'bgpPeerRemoteAs' => $routing['bgpPeerRemoteAs'],
        'bgpPeerDescr' => $routing['bgpPeerDescr']);
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
