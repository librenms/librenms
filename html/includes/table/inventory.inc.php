<?php

use LibreNMS\Authentication\LegacyAuth;

$where = '1';
$param = array();



if (LegacyAuth::user()->hasGlobalRead()) {
    $sql = " FROM entPhysical AS E, devices AS D WHERE $where AND D.device_id = E.device_id";
} else {
    $sql     = " FROM entPhysical AS E, devices AS D, devices_perms AS P WHERE $where AND D.device_id = E.device_id AND P.device_id = D.device_id AND P.user_id = ?";
    $param[] = LegacyAuth::id();
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `E`.`entPhysicalDescr` LIKE '%$searchPhrase%' OR `E`.`entPhysicalModelName` LIKE '%$searchPhrase%' OR `E`.`entPhysicalSerialNum` LIKE '%$searchPhrase%')";
}

if (isset($vars['string']) && strlen($vars['string'])) {
    $sql    .= ' AND E.entPhysicalDescr LIKE ?';
    $param[] = '%'.$vars['string'].'%';
}

if (isset($vars['device_string']) && strlen($vars['device_string'])) {
    $sql    .= ' AND D.hostname LIKE ?';
    $param[] = '%'.$vars['device_string'].'%';
}

if (isset($vars['part']) && strlen($vars['part'])) {
    $sql    .= ' AND E.entPhysicalModelName = ?';
    $param[] = $vars['part'];
}

if (isset($vars['serial']) && strlen($vars['serial'])) {
    $sql    .= ' AND E.entPhysicalSerialNum LIKE ?';
    $param[] = '%'.$vars['serial'].'%';
}

if (isset($vars['device']) && is_numeric($vars['device'])) {
    $sql    .= ' AND D.device_id = ?';
    $param[] = $vars['device'];
}

$count_sql = "SELECT COUNT(`entPhysical_id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`hostname` DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `D`.`device_id` AS `device_id`, `D`.`hostname` AS `hostname`,`entPhysicalDescr` AS `description`, `entPhysicalName` AS `name`, `entPhysicalModelName` AS `model`, `entPhysicalSerialNum` AS `serial` $sql";

foreach (dbFetchRows($sql, $param) as $invent) {
    $response[] = array(
                   'hostname'    => generate_device_link($invent, shortHost($invent['hostname'])),
                   'description' => $invent['description'],
                   'name'        => $invent['name'],
                   'model'       => $invent['model'],
                   'serial'      => $invent['serial'],
                  );
}

$output = array(
           'current'  => $current,
           'rowCount' => $rowCount,
           'rows'     => $response,
           'total'    => $total,
          );
echo _json_encode($output);
