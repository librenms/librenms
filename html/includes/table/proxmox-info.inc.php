<?php

if (!empty($_POST['device_id'])) {
    $device_id = $_POST['device_id'];
}

$param = array($device_id);

$sql = " FROM `proxmox` WHERE `device_id` = ?";
$count_sql = "SELECT COUNT(*) " . $sql;

$total = dbFetchCell($count_sql, $param);

if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`vmid` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = 'SELECT `description`,`vmstatus`,`cluster`,`vmid`, `last_seen`, `vmpid`, `vmmem`, `vmmaxmem`, `vmmemuse`, `vmcpus`, `vmdisk`, `vmmaxdisk`, `vmdiskuse`, `vmuptime`, `vmtype`' . $sql;
$response = dbFetchRows($sql, $param);

$output = array(
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
);

echo _json_encode($output);
