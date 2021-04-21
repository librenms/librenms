<?php

$rrd_filename = Rrd::name($device['hostname'], 'sap-' . $vars['traffic_id']);

$stats = [
    'sapIngressBits' => 'Ingress Bits',
    'sapEgressBits' => 'Egress Bits',
    'sapIngressDroppedBi' => 'Ingress Drops Bits',
    'sapEgressDroppedBit' => 'Egress Drops Bits',
];

foreach ($stats as $stat => $descr) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $stat;
    if (strpos($stat, 'Out') !== false || strpos($stat, 'Retrans') !== false || strpos($stat, 'Attempt') !== false) {
        $rrd_list[$i]['invert'] = true;
    }
}

$colours = 'mixed';

$unit_text = 'SAP Traffic';

require 'includes/html/graphs/generic_multi_line.inc.php';
