<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_capacity_clients');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'MaxClient' => 'Max Client',
    'MaxClientless' => 'Max Clientless',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Capacity Clients';

$units = 'clients';
$total_units = 'Clients';
$colours = 'psychedelic';

$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';;