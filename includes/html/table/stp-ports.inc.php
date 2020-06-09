<?php

$device_id = mres($vars['device_id']);

$param[] = $device_id;

$sql = " FROM `ports_stp` `ps` JOIN `ports` `p` ON `ps`.`port_id`=`p`.`port_id` WHERE `ps`.`device_id` = ?";

$count_sql = "SELECT COUNT(*) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
        $total = 0;
}

if (!isset($sort) || empty($sort)) {
        $sort = 'port_id DESC';
}

$sql .= " ORDER BY ps.$sort";

if (isset($current)) {
        $limit_low  = (($current * $rowCount) - ($rowCount));
            $limit_high = $rowCount;
}

if ($rowCount != -1) {
        $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `ps`.*, `p`.* $sql";

foreach (dbFetchRows($sql, array($device_id)) as $stp_ports_db) {
    $stp_ports_db = cleanPort($stp_ports_db);
    $bridge_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedBridge']));
    $root_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedRoot']));

    $response[] = array (
        'port_id'            => generate_port_link($stp_ports_db, $stp_ports_db['ifName'])."<br>".$stp_ports_db['ifAlias'],
        'priority'           => $stp_ports_db['priority'],
        'state'              => $stp_ports_db['state'],
        'enable'             => $stp_ports_db['enable'],
        'pathCost'           => $stp_ports_db['pathCost'],
        'designatedRoot'     => generate_device_link($root_device, $root_device['hostname'])."<br>".$stp_ports_db['designatedRoot'],
        'designatedCost'     => $stp_ports_db['designatedCost'],
        'designatedBridge'   => generate_device_link($bridge_device, $bridge_device['hostname'])."<br>".$stp_ports_db['designatedBridge'],
        'designatedPort'     => $stp_ports_db['designatedPort'],
        'forwardTransitions' => $stp_ports_db['forwardTransitions']
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
