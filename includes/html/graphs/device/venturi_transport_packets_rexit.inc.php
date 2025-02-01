<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_packets_rexit');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'Retransmitted' => 'Retransmitted',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Packets';
$total_units = 'count';
$colours = 'mega';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
