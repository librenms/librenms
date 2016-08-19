<?php

if (!is_array($config['customers_descr'])) {
    $config['customers_descr'] = array($config['customers_descr']);
}

$descr_type = "'".implode("', '", $config['customers_descr'])."'";

$i = 0;

$sql = ' FROM `ports` LEFT JOIN `devices` AS `D` ON `ports`.`device_id` = `D`.`device_id` WHERE `port_descr_type` IN (?)';
$param[] = array($descr_type);
if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`port_descr_descr` LIKE '%$searchPhrase%' OR `ifName` LIKE '%$searchPhrase%' OR `ifDescr` LIKE '%$searchPhrase%' OR `ifAlias` LIKE '%$searchPhrase%' OR `D`.`hostname` LIKE '%$searchPhrase%' OR `port_descr_speed` LIKE '%$searchPhrase%' OR `port_descr_notes` LIKE '%$searchPhrase%')";
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

$sql = "SELECT * $sql";

foreach (dbFetchRows($sql, $param) as $customer) {
    $i++;

    $customer_name = $customer['port_descr_descr'];

    foreach (dbFetchRows('SELECT * FROM `ports` WHERE `port_descr_type` IN (?) AND `port_descr_descr` = ?', array(array($descr_type), $customer['port_descr_descr'])) as $port) {
        $device = device_by_id_cache($port['device_id']);

        $ifname  = fixifname($device['ifDescr']);
        $ifclass = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

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
