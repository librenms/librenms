<?php

$device_id = mres($vars['device_id']);

$sql = " FROM `storage` AS `S` LEFT JOIN `devices` AS `D` ON `S`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=? AND `S`.`storage_deleted`=0";
$param[] = $device_id;

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `S`.`storage_descr` LIKE '%$searchPhrase%' OR `S`.`storage_perc` LIKE '%$searchPhrase%' OR `S`.`storage_perc_warn` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`storage_id`) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `S`.`storage_descr`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

//$response[] = array('storage_descr' => $sql);
foreach (dbFetchRows($sql, $param) as $drive) {
    $perc = round($drive['storage_perc'], 0);
    $perc_warn = round($drive['storage_perc_warn'], 0);
    $response[] = array(
        'storage_id' => $drive['storage_id'],
        'hostname' => generate_device_link($drive),
        'storage_descr' => $drive['storage_descr'],
        'storage_perc' => $perc . "%",
        'storage_perc_warn' => $perc_warn);
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
