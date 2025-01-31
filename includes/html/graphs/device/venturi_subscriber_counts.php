<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_subscriber_counts');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'TotalClientCount' => 'Total Client',
    'TotalClientlessCount' => 'Total Clientless',
    'CurrentClientCount' => 'Current Client',
    'CurrentClientlessCount' => 'Current Clientless',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$unit_text = 'Counts';

$total_units = 'Clients';
$colours = 'mixed';

$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';