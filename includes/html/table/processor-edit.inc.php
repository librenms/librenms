<?php

$device_id = $vars['device_id'];

$sql = ' FROM `processors` AS `P` LEFT JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=?';
$param[] = $device_id;

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `P`.`processor_descr` LIKE ? OR `S`.`processor_usage` LIKE ? OR `P`.`processor_perc_warn` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(`processor_id`) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `P`.`processor_descr`';
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

foreach (dbFetchRows($sql, $param) as $drive) {
    $perc = round($drive['processor_usage'], 0);
    $perc_warn = round($drive['processor_perc_warn'], 0);
    $response[] = [
        'processor_id' => $drive['processor_id'],
        'hostname' => generate_device_link($drive),
        'processor_descr' => $drive['processor_descr'],
        'processor_perc' => $perc . '%',
        'processor_perc_warn' => $perc_warn, ];
}

$output = ['current'=>$current, 'rowCount'=>$rowCount, 'rows'=>$response, 'total'=>$total];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
