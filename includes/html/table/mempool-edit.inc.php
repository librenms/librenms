<?php

$device_id = $vars['device_id'];

$sql = ' FROM `mempools` AS `M` LEFT JOIN `devices` AS `D` ON `M`.`device_id` = `D`.`device_id` WHERE `D`.`device_id`=?';
$param[] = $device_id;

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `M`.`mempool_descr` LIKE ? OR `S`.`mempool_perc` LIKE ? OR `M`.`mempool_perc_warn` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(`mempool_id`) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `M`.`mempool_descr`';
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
    $perc = round($drive['mempool_perc'], 0);
    $perc_warn = round($drive['mempool_perc_warn'], 0);
    $response[] = [
        'mempool_id' => $drive['mempool_id'],
        'hostname' => generate_device_link($drive),
        'mempool_descr' => $drive['mempool_descr'],
        'mempool_perc' => $perc . '%',
        'mempool_perc_warn' => $perc_warn, ];
}

$output = ['current'=>$current, 'rowCount'=>$rowCount, 'rows'=>$response, 'total'=>$total];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
