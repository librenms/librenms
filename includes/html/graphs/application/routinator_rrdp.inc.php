<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$scale_min = 0;
$unit_text = 'Repos';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'routinator', $app->app_id, 'repos']);

$array = [
    'rrdp_total' => ['descr' => 'Total'],
    'rrdp_ok' => ['descr' => 'OK'],
    'rrdp_failed' => ['descr' => 'Failed'],
    'rrdp_unreachable' => ['descr' => 'Unreachable'],
];

$i = 0;
$rrd_list = [];
foreach ($array as $ds => $var) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $var['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$i");
    $i++;
}

require 'includes/html/graphs/generic_multi_line.inc.php';
