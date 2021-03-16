<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

$where = '1';

if (is_numeric($vars['device'])) {
    $where .= ' AND E.device_id = ?';
    $param[] = (int) $vars['device'];
}

if (! empty($vars['eventtype'])) {
    $where .= ' AND `E`.`type` = ?';
    $param[] = $vars['eventtype'];
}

if ($vars['string']) {
    $where .= ' AND E.message LIKE ?';
    $param[] = '%' . $vars['string'] . '%';
}

if (Auth::user()->hasGlobalRead()) {
    $sql = " FROM `eventlog` AS E LEFT JOIN `devices` AS `D` ON `E`.`device_id`=`D`.`device_id` WHERE $where";
} else {
    $sql = " FROM `eventlog` AS E, devices_perms AS P WHERE $where AND E.device_id = P.device_id AND P.user_id = ?";
    $param[] = Auth::id();
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `D`.`sysName` LIKE ? OR `E`.`datetime` LIKE ? OR `E`.`message` LIKE ? OR `E`.`type` LIKE ? OR `E`.`username` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(event_id) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = 'datetime DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `E`.*,DATE_FORMAT(datetime, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') as humandate,severity $sql";

foreach (dbFetchRows($sql, $param) as $eventlog) {
    $dev = device_by_id_cache($eventlog['device_id']);
    if ($eventlog['type'] == 'interface') {
        $this_if = cleanPort(getifbyid($eventlog['reference']));
        $type = '<b>' . generate_port_link($this_if, makeshortif(strtolower($this_if['label']))) . '</b>';
    } else {
        $type = $eventlog['type'];
    }
    $severity_colour = $eventlog['severity'];

    if ($eventlog['username'] == '') {
        $eventlog['username'] = 'System';
    }

    $response[] = [
        'datetime' => "<span class='alert-status " . eventlog_severity($severity_colour) . " eventlog-status'></span><span style='display:inline;'>" . $eventlog['humandate'] . '</span>',
        'hostname' => generate_device_link($dev, shorthost($dev['hostname'])),
        'type' => $type,
        'message' => htmlspecialchars($eventlog['message']),
        'username' => $eventlog['username'],
    ];
}

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
