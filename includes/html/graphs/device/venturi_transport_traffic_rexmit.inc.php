<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_subscriber_traffic');

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

$multiplier = 8;
$unit_text = 'Traffic';
$colours = 'mega';
$simple_rrd = 1;

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
