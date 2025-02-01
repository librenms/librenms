<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_undeliverables');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'UndeliverableToClients' => 'ToClient',
    'UndeliverableToComp' => 'ToCompressor',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Undeliverables';
$total_units = 'Count';
$colours = 'mega';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
