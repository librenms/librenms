<?php

use LibreNMS\Config;

$param = [];
$sql = ' FROM `devices` AS D ';

if (! Auth::user()->hasGlobalAdmin()) {
    $sql .= ', devices_perms AS P ';
}

$sql .= ' LEFT JOIN `locations` as L ON `D`.`location_id`=`L`.`id`';
$sql .= ' LEFT JOIN `poller_groups` ON `D`.`poller_group`=`poller_groups`.`id`';

if (! Auth::user()->hasGlobalAdmin()) {
    $sql .= " WHERE D.device_id = P.device_id AND P.user_id = '" . Auth::id() . "' AND D.ignore = '0'";
} else {
    $sql .= ' WHERE 1';
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (hostname LIKE ? OR sysName LIKE ? OR last_polled LIKE ? OR last_polled_timetaken LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

if ($vars['type'] == 'unpolled') {
    $overdue = (int) (Config::get('rrd.step', 300) * 1.2);
    $sql .= " AND `last_polled` <= DATE_ADD(NOW(), INTERVAL - $overdue SECOND)";
}

if (! isset($sort) || empty($sort)) {
    $sort = 'last_polled_timetaken DESC';
}

$sql .= " AND D.status ='1' AND D.ignore='0' AND D.disabled='0'";

$count_sql = "SELECT COUNT(`D`.`device_id`) $sql";

$sql .= " ORDER BY $sort";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT D.device_id, L.location as `location`, D.hostname AS `hostname`, D.sysName, D.last_polled AS `last_polled`, `group_name`, D.last_polled_timetaken AS `last_polled_timetaken` $sql";

foreach (dbFetchRows($sql, $param) as $device) {
    if (empty($device['group_name'])) {
        $device['group_name'] = 'General';
    }
    $response[] = [
        'hostname'              => generate_device_link($device, null, ['tab' => 'graphs', 'group' => 'poller']),
        'last_polled'           => $device['last_polled'],
        'poller_group'          => $device['group_name'],
        'location'              => $device['location'],
        'last_polled_timetaken' => round($device['last_polled_timetaken'], 2),
    ];
}

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output);
