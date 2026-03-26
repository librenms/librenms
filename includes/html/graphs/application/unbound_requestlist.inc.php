<?php

require 'includes/html/graphs/common.inc.php';

$i = 0;
$scale_min = 0;
$nototal = 1;
$unit_text = 'Query/s';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'unbound-requestlist', $app->app_id]);
$array = [
    'max',
    'overwritten',
    'exceeded',
];

$colours = 'mixed';
$rrd_list = [];

foreach ($array as $ds) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = strtoupper($ds);
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

require 'includes/html/graphs/generic_multi_line.inc.php';
