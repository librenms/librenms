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

$where = 1;

if (is_numeric($vars['device_id'])) {
    $where .= ' AND E.device_id = ?';
    $param[] = $vars['device_id'];
}

if ($vars['state'] >= 0) {
    $where .= ' AND `E`.`state` = ?';
    $param[] = mres($vars['state']);
}

if (Auth::user()->hasGlobalRead()) {
    $sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id WHERE $where";
} else {
    $sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id RIGHT JOIN devices_perms AS P ON E.device_id = P.device_id WHERE $where AND P.user_id = ?";
    $param[] = array(Auth::id());
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `D`.`sysName` LIKE '%$searchPhrase%' OR `E`.`time_logged` LIKE '%$searchPhrase%' OR `name` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`E`.`id`) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = 'time_logged DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT D.device_id,name AS alert,rule_id, state,time_logged,DATE_FORMAT(time_logged, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') as humandate,details $sql";

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $dev = device_by_id_cache($alertlog['device_id']);
    logfile($alertlog['rule_id']);
    $log = dbFetchCell('SELECT details FROM alert_log WHERE rule_id = ? AND device_id = ? AND `state` = 1 ORDER BY id DESC LIMIT 1', array($alertlog['rule_id'], $alertlog['device_id']));
    $fault_detail = alert_details($log);
    if (empty($fault_detail)) {
        $fault_detail = 'Rule created, no faults found';
    }
    $alert_state = $alertlog['state'];
    if ($alert_state == '0') {
        $status = 'label-success';
    } elseif ($alert_state == '1') {
        $status = 'label-danger';
    } elseif ($alert_state == '2') {
        $status = 'label-info';
    } elseif ($alert_state == '3') {
        $status = 'label-warning';
    } elseif ($alert_state == '4') {
        $status = 'label-primary';
    }//end if


    $response[] = array(
        'id' => $rulei++,
        'time_logged' => $alertlog['humandate'],
        'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . ($rulei) . '" data-parent="#alerts"></a>',
        'hostname' => '<div class="incident">' . generate_device_link($dev, shorthost($dev['hostname'])) . '<div id="incident' . ($rulei) . '" class="collapse">' . $fault_detail . '</div></div>',
        'alert' => htmlspecialchars($alertlog['alert']),
        'status' => "<i class='alert-status " . $status . "'></i>"
    );
}//end foreach

$output = array(
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
);
echo _json_encode($output);
