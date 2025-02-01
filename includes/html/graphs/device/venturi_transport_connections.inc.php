<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_connections');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'TotalConnections' => 'Total',
    'CurrentConnections' => 'Current',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Connections';
$total_units = 'Count';
$colours = 'mega';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
