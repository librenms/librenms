<?php

use LibreNMS\Config;

$cust_descrs = (array)Config::get('customers_descr', ['cust']);

$sql = ' FROM `ports` LEFT JOIN `devices` AS `D` ON `ports`.`device_id` = `D`.`device_id` WHERE `port_descr_type` IN ' .  dbGenPlaceholders(count($cust_descrs));
$param = $cust_descrs;

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`port_descr_descr` LIKE ? OR `ifName` LIKE ? OR `ifDescr` LIKE ? OR `ifAlias` LIKE ? OR `D`.`hostname` LIKE ? OR `port_descr_speed` LIKE ? OR `port_descr_notes` LIKE ?)";
    array_push($param, "%$searchPhrase%", "%$searchPhrase%", "%$searchPhrase%", "%$searchPhrase%", "%$searchPhrase%", "%$searchPhrase%", "%$searchPhrase%");
}

$count_sql = "SELECT COUNT(DISTINCT(`port_descr_descr`)) $sql";

$sql .= ' GROUP BY `port_descr_descr`';

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`port_descr_descr`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `port_descr_descr` $sql";

foreach (dbFetchRows($sql, $param) as $customer) {
    $customer_name = $customer['port_descr_descr'];

    $port_query = 'SELECT * FROM `ports` WHERE `port_descr_type` IN ' . dbGenPlaceholders(count($cust_descrs)) .  ' AND `port_descr_descr` = ?';
    $port_params = $cust_descrs;
    $port_params[] = $customer_name;

    foreach (dbFetchRows($port_query, $port_params) as $port) {
        $device = device_by_id_cache($port['device_id']);

        $ifname  = fixifname($device['ifDescr']);
        $ifclass = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);
        $port    = cleanPort($port);

        if ($device['os'] == 'ios') {
            if ($port['ifTrunk']) {
                $vlan = '<span class=box-desc><span class=red>'.$port['ifTrunk'].'</span></span>';
            } elseif ($port['ifVlan']) {
                $vlan = '<span class=box-desc><span class=blue>VLAN '.$port['ifVlan'].'</span></span>';
            } else {
                $vlan = '';
            }
        }

        $response[] = array(
            'port_descr_descr'           => $customer_name,
            'device_id'          => generate_device_link($device),
            'ifDescr'            => generate_port_link($port, makeshortif($port['ifDescr'])),
            'port_descr_speed'   => $port['port_descr_speed'],
            'port_descr_circuit' => $port['port_descr_circuit'],
            'port_descr_notes'   => $port['port_descr_notes'],

        );

        unset($customer_name);
    }


    $graph_array['type']   = 'customer_bits';
    $graph_array['height'] = '100';
    $graph_array['width']  = '220';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $customer['port_descr_descr'];

    $return_data = true;
    include 'includes/print-graphrow.inc.php';
    $response[] = array(
        'port_descr_descr'           => $graph_data[0],
        'device_id'          => $graph_data[1],
        'ifDescr'            => '',
        'port_descr_speed'   => '',
        'port_descr_circuit' => $graph_data[2],
        'port_descr_notes'   => $graph_data[3],
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);

echo _json_encode($output);
