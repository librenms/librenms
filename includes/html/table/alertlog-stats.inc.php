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
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

$alert_severities = array(
    // alert_rules.status is enum('ok','warning','critical')
    'ok' => 1,
    'warning' => 2,
    'critical' => 3,
    'ok only' => 4,
    'warning only' => 5,
    'critical only' => 6,
);

$where = 1;
$params = [];

if (is_numeric($vars['device_id'])) {
    $where .= ' AND E.device_id = ?';
    $param[] = $vars['device_id'];
}

$where .= ' AND `E`.`state` = 1'; // state 1 => alert

if (is_numeric($vars['time_interval'])) {
    $where .= ' AND E.`time_logged` > DATE_SUB(NOW(),INTERVAL ? DAY)';
    $param[] = $vars['time_interval'];
}

$alert_rules = array();
$sql = "SELECT id, name, severity from alert_rules";
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $alert_rules[$alertlog['id']]['alert'] = $alertlog['name'];
    $alert_rules[$alertlog['id']]['severity'] = $alert_severities[$alertlog['severity']];
}

if (isset($vars['min_severity'])) {
    $rules_count = 0;
    foreach($alert_rules as $id => $ruledat) {
        if (($vars['min_severity']>3 && $ruledat['severity'] == ($vars['min_severity'] - 3)) || ($vars['min_severity']<=3 && $ruledat['severity'] >= $vars['min_severity'])) {
            $param[] = $id;
            $rules_count++;
        }
    }
    if ($rules_count > 0) {
        $where .= " AND rule_id in " . dbGenPlaceholders($rules_count);
    } else {
        $where .= " AND rule_id = 0";
    }
}

$sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id ";
if (Auth::user()->hasGlobalRead()) {
    $sql .= "WHERE $where";
} else {
    $sql .= "RIGHT JOIN devices_perms AS P ON E.device_id = P.device_id WHERE $where AND P.user_id = ?";
    $param[] = array(Auth::id());
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE ? OR `D`.`sysName` LIKE ? OR `E`.`time_logged` LIKE ? OR `name` LIKE ?)";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(DISTINCT D.sysname, rule_id) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

$sql .= " GROUP BY D.device_id, rule_id ORDER BY COUNT(*) DESC";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT COUNT(*), D.device_id, rule_id $sql";

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $dev = device_by_id_cache($alertlog['device_id']);

    $response[] = array(
        'id' => $rulei++,
        'count' => $alertlog['COUNT(*)'],
        'hostname' => '<div class="incident">' . generate_device_link($dev, shorthost($dev['hostname'])),
        'alert_rule' => $alert_rules[$alertlog['rule_id']]['alert'],
    );
}//end foreach

$output = array(
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
);
echo _json_encode($output);
