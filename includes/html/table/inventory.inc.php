<?php

$where = '1';
$param = [];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$sql = " FROM entPhysical AS E, devices AS D WHERE $where AND D.device_id = E.device_id";

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `E`.`entPhysicalDescr` LIKE ? OR `E`.`entPhysicalModelName` LIKE ? OR `E`.`entPhysicalSerialNum` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

if (isset($vars['string']) && strlen($vars['string'])) {
    $sql .= ' AND E.entPhysicalDescr LIKE ?';
    $param[] = '%' . $vars['string'] . '%';
}

if (isset($vars['device_string']) && strlen($vars['device_string'])) {
    $sql .= ' AND D.hostname LIKE ?';
    $param[] = '%' . $vars['device_string'] . '%';
}

if (isset($vars['part']) && strlen($vars['part'])) {
    $sql .= ' AND E.entPhysicalModelName = ?';
    $param[] = $vars['part'];
}

if (isset($vars['serial']) && strlen($vars['serial'])) {
    $sql .= ' AND E.entPhysicalSerialNum LIKE ?';
    $param[] = '%' . $vars['serial'] . '%';
}

if (isset($vars['device']) && is_numeric($vars['device'])) {
    $sql .= ' AND D.device_id = ?';
    $param[] = $vars['device'];
}

$count_sql = "SELECT COUNT(`entPhysical_id`) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`hostname` DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `D`.`device_id` AS `device_id`, `D`.`os` AS `os`, `D`.`hostname` AS `hostname`, `D`.`sysName` AS `sysName`,`entPhysicalDescr` AS `description`, `entPhysicalName` AS `name`, `entPhysicalModelName` AS `model`, `entPhysicalSerialNum` AS `serial` $sql";

foreach (dbFetchRows($sql, $param) as $invent) {
    $response[] = [
        'hostname'    => generate_device_link($invent),
        'description' => $invent['description'],
        'name'        => $invent['name'],
        'model'       => $invent['model'],
        'serial'      => $invent['serial'],
    ];
}

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
