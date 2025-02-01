<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_subscriber_traffic');

require 'includes/html/graphs/common.inc.php';

$i = 0;

foreach ([
    'ClientTraffic' => 'TotClient',
    'ClientlessTraffic' => 'TotClientless',
] as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

$units = 'KB';
$unit_text = 'KBps';

$total_units = 'Traffic';
$colours = 'mixed';

$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
