<?php

use LibreNMS\Config;
use LibreNMS\Authentication\LegacyAuth;

$sql = ' FROM `devices` AS D ';

if (!LegacyAuth::user()->hasGlobalAdmin()) {
    $sql .= ", devices_perms AS P ";
}

$sql .= " LEFT JOIN `poller_groups` ON `D`.`poller_group`=`poller_groups`.`id`";

if (!LegacyAuth::user()->hasGlobalAdmin()) {
    $sql .= " WHERE D.device_id = P.device_id AND P.user_id = '".LegacyAuth::id()."' AND D.ignore = '0'";
} else {
    $sql .= ' WHERE 1';
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (hostname LIKE '%$searchPhrase%' OR sysName LIKE '%$searchPhrase%' OR last_polled LIKE '%$searchPhrase%' OR last_polled_timetaken LIKE '%$searchPhrase%')";
}

if ($vars['type'] == "unpolled") {
    $overdue = (int)(Config::get('rrd.step', 300) * 1.2);
    $sql .= " AND `last_polled` <= DATE_ADD(NOW(), INTERVAL - $overdue SECOND)";
}

if (!isset($sort) || empty($sort)) {
    $sort = 'last_polled_timetaken DESC';
}

$sql .= " AND D.status ='1' AND D.ignore='0' AND D.disabled='0'";

$count_sql = "SELECT COUNT(`D`.`device_id`) $sql";

$sql .= " ORDER BY $sort";

$total     = dbFetchCell($count_sql);
if (empty($total)) {
    $total = 0;
}

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT D.device_id, D.hostname AS `hostname`, D.sysName, D.last_polled AS `last_polled`, `group_name`, D.last_polled_timetaken AS `last_polled_timetaken` $sql";

foreach (dbFetchRows($sql, array()) as $device) {
    if (empty($device['group_name'])) {
        $device['group_name'] = 'General';
    }
    $response[] = array(
        'hostname'              => "<a class='list-device' href='".generate_device_url($device, array('tab' => 'graphs', 'group' => 'poller'))."'>".format_hostname($device).'</a>',
        'last_polled'           => $device['last_polled'],
        'poller_group'          => $device['group_name'],
        'last_polled_timetaken' => $device['last_polled_timetaken'],
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo json_encode($output);
