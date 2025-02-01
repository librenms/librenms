<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_subscriber_counts');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'TotalClientCount' => 'TotClient',
    'TotalClientlessCount' => 'TotClientless',
    'CurrentClientCount' => 'CurClient',
    'CurrentClientlessCount' => 'CurClientless',
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
