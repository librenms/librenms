<?php

$where = '1';
$param = array();



if ($_SESSION['userlevel'] >= '5') {
    $sql = " FROM entPhysical AS E, devices AS D WHERE $where AND D.device_id = E.device_id";
} else {
    $sql     = " FROM entPhysical AS E, devices AS D, devices_perms AS P WHERE $where AND D.device_id = E.device_id AND P.device_id = D.device_id AND P.user_id = ?";
    $param[] = $_SESSION['user_id'];
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `E`.`entPhysicalDescr` LIKE '%$searchPhrase%' OR `E`.`entPhysicalModelName` LIKE '%$searchPhrase%' OR `E`.`entPhysicalSerialNum` LIKE '%$searchPhrase%')";
}

if (isset($_POST['string']) && strlen($_POST['string'])) {
    $sql    .= ' AND E.entPhysicalDescr LIKE ?';
    $param[] = '%'.$_POST['string'].'%';
}

if (isset($_POST['device_string']) && strlen($_POST['device_string'])) {
    $sql    .= ' AND D.hostname LIKE ?';
    $param[] = '%'.$_POST['device_string'].'%';
}

if (isset($_POST['part']) && strlen($_POST['part'])) {
    $sql    .= ' AND E.entPhysicalModelName = ?';
    $param[] = $_POST['part'];
}

if (isset($_POST['serial']) && strlen($_POST['serial'])) {
    $sql    .= ' AND E.entPhysicalSerialNum LIKE ?';
    $param[] = '%'.$_POST['serial'].'%';
}

if (isset($_POST['device']) && is_numeric($_POST['device'])) {
    $sql    .= ' AND D.device_id = ?';
    $param[] = $_POST['device'];
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
