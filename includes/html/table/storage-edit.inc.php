<?php

$device_id = $vars['device_id'];

$sql = ' FROM `storage` AS `S` LEFT JOIN `devices` AS `D` ON `S`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=? AND `S`.`storage_deleted`=0';
$param[] = $device_id;

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `S`.`storage_descr` LIKE ? OR `S`.`storage_perc` LIKE ? OR `S`.`storage_perc_warn` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(`storage_id`) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
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
    $perc = round($drive['storage_perc']);
    $perc_warn = round($drive['storage_perc_warn']);
    $size = \LibreNMS\Util\Number::formatBi($drive['storage_size']);
    $response[] = [
        'storage_id' => $drive['storage_id'],
        'hostname' => generate_device_link($drive),
        'storage_descr' => $drive['storage_descr'],
        'storage_perc' => $perc . '%',
        'storage_perc_warn' => $perc_warn,
        'storage_size' => $size,
    ];
}

$output = ['current'=>$current, 'rowCount'=>$rowCount, 'rows'=>$response, 'total'=>$total];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
