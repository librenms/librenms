<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_capacity_bandwidth');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'MaxTcpBandwidth' => 'TCP',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Capacity';
$total_units = 'Bits';
$colours = 'mixed';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
