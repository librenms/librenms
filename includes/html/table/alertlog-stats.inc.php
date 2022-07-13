<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage graphs
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

$where = 1;

if (is_numeric($vars['device_id'])) {
    $where .= ' AND E.device_id = ?';
    $param[] = $vars['device_id'];
}

$where .= ' AND `E`.`state` = 1'; // state 1 => alert

if (is_numeric($vars['time_interval'])) {
    $where .= ' AND E.`time_logged` > DATE_SUB(NOW(),INTERVAL ? DAY)';
    $param[] = $vars['time_interval'];
}

if (isset($vars['min_severity'])) {
    $where .= get_sql_filter_min_severity($vars['min_severity'], 'R');
}

if (Auth::user()->hasGlobalRead()) {
    $sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id WHERE $where";
} else {
    $sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id RIGHT JOIN devices_perms AS P ON E.device_id = P.device_id WHERE $where AND P.user_id = ?";
    $param[] = [Auth::id()];
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `D`.`sysName` LIKE ? OR `E`.`time_logged` LIKE ? OR `name` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(DISTINCT D.sysname, R.name) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

$sql .= ' GROUP BY D.device_id, R.name ORDER BY COUNT(*) DESC';

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT COUNT(*), D.device_id, R.name $sql";

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $dev = device_by_id_cache($alertlog['device_id']);

    $response[] = [
        'id' => $rulei++,
        'count' => $alertlog['COUNT(*)'],
        'hostname' => '<div class="incident">' . generate_device_link($dev),
        'alert_rule' => $alertlog['name'],
    ];
}//end foreach

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
