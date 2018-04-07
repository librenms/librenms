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
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\Auth;

$where = '1';
$param = array();

if (!empty($vars['searchPhrase'])) {
    $where .= ' AND S.msg LIKE "%'.mres($vars['searchPhrase']).'%"';
}

if ($vars['program']) {
    $where  .= ' AND S.program = ?';
    $param[] = $vars['program'];
}

if (is_numeric($vars['device'])) {
    $where  .= ' AND S.device_id = ?';
    $param[] = $vars['device'];
}

if ($vars['priority']) {
    $where  .= ' AND S.priority = ?';
    $param[] = $vars['priority'];
}

if (!empty($vars['from'])) {
    $where  .= ' AND timestamp >= ?';
    $param[] = $vars['from'];
}

if (!empty($vars['to'])) {
    $where  .= ' AND timestamp <= ?';
    $param[] = $vars['to'];
}

if (Auth::user()->hasGlobalRead()) {
    $sql  = 'FROM syslog AS S';
    $sql .= ' WHERE '.$where;
} else {
    $sql   = 'FROM syslog AS S, devices_perms AS P ';
    $sql  .= 'WHERE S.device_id = P.device_id AND P.user_id = ? AND ';
    $sql  .= $where;
    $param = array_merge(array(Auth::id()), $param);
}

$count_sql = "SELECT COUNT(*) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = 'timestamp DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT S.*, DATE_FORMAT(timestamp, '".$config['dateformat']['mysql']['compact']."') AS date $sql";

foreach (dbFetchRows($sql, $param) as $syslog) {
    $dev        = device_by_id_cache($syslog['device_id']);
    $response[] = array(
        'label'  => generate_priority_label($syslog['priority']),
        'timestamp' => $syslog['date'],
        'level'      => $syslog['priority'],
        'device_id' => generate_device_link($dev, shorthost($dev['hostname'])),
        'program'   => $syslog['program'],
        'msg'       => display($syslog['msg']),
        'priority'   => generate_priority_status($syslog['priority']),
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
