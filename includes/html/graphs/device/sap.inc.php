<?php
$rrd_filename = rrd_name($device['hostname'], 'sap-' . $vars['traffic_id']);

$stats = [
    'sapIngressBytes',
    'sapEgressBytes',
    'sapIngressDroppedBy',
    'sapEgressDroppedByt',
];

$i = 0;
foreach ($stats as $stat) {
    $i++;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = str_replace('udp', '', $stat);
    $rrd_list[$i]['ds'] = $stat;
    if (strpos($stat, 'Out') !== false || strpos($stat, 'Retrans') !== false || strpos($stat, 'Attempt') !== false) {
        $rrd_list[$i]['invert'] = true;
    }
}

$colours = 'mixed';

$unit_text = 'SAP Traffic';

require 'includes/html/graphs/generic_multi_line.inc.php';
