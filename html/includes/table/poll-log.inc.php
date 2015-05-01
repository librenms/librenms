<?php

$sql = " FROM `devices` AS D";

if (is_admin() === FALSE) {
    $sql .= ", devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.ignore = '0'";
} else {
    $sql .= " WHERE 1";
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (hostname LIKE '%$searchPhrase%' OR last_polled LIKE '%$searchPhrase%' OR last_polled_timetaken LIKE '%$searchPhrase%')";
}

if (!isset($sort) || empty($sort)) {
    $sort = 'last_polled_timetaken DESC';
}

$count_sql = "SELECT COUNT(`D`.`device_id`) $sql";
$total = dbFetchCell($count_sql);
if (empty($total)) {
    $total = 0;
}

$sql .= " AND D.status ='1' AND D.ignore='0' AND D.disabled='0' ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT D.device_id,D.hostname AS `hostname`, D.last_polled AS `last_polled`, D.last_polled_timetaken AS `last_polled_timetaken` $sql";

foreach (dbFetchRows($sql) as $device) {
    $response[] = array('hostname' => "<a class='list-device' href='" .generate_device_url($device, array('tab' => 'graphs', 'group' => 'poller')). "'>" .$device['hostname']. "</a>",
                        'last_polled' => $device['last_polled'],
                        'last_polled_timetaken' => $device['last_polled_timetaken']);
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);

?>
