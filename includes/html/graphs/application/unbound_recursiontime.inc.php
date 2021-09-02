<?php

require 'includes/html/graphs/common.inc.php';

$i = 0;
$scale_min = 0;
$nototal = 1;
$unit_text = 'Time in ms';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'unbound-recursiontime', $app['app_id']]);
$array = [
    'avg',
    'median',
];

$colours = 'mixed';
$rrd_list = [];

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = strtoupper($ds);
        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
