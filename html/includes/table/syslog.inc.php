<?php

$where = '';

if (!empty($_POST['searchPhrase'])) {
    $where .= 'S.msg LIKE "%'.mres($_POST['searchPhrase']).'%" AND ';
}

if ($_POST['program']) {
    $where  .= 'S.program = ? AND ';
    $param[] = $_POST['program'];
}

if (is_numeric($_POST['device'])) {
    $where  .= ' S.device_id = ? AND ';
    $param[] = $_POST['device'];
}

if (!empty($_POST['from'])) {
    $where  .= 'timestamp >= ? AND ';
    $param[] = $_POST['from'];
}

if (!empty($_POST['to'])) {
    $where  .= 'timestamp <= ? AND ';
    $param[] = $_POST['to'];
}

if ($_SESSION['userlevel'] >= '5') {
    $sql  = 'FROM syslog AS S';
    $sql .= ' WHERE '.$where;
}
else {
    $sql   = 'FROM syslog AS S, devices_perms AS P ';
    $sql  .= 'WHERE S.device_id = P.device_id AND P.user_id = ? AND ';
    $sql  .= $where . "1";
    $param = array_merge(array($_SESSION['user_id']), $param);
}

$count_sql = "SELECT COUNT(timestamp) $sql";
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
        'timestamp' => $syslog['date'],
        'device_id' => generate_device_link($dev, shorthost($dev['hostname'])),
        'program'   => $syslog['program'],
        'msg'       => htmlspecialchars($syslog['msg']),
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
