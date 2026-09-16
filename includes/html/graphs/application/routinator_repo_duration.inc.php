<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$scale_min = 0;
$unit_text = 'Seconds';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'routinator', $app->app_id, 'repos']);

$array = [
    'rrdp_duration_max' => ['descr' => 'RRDP max'],
    'rsync_duration_max' => ['descr' => 'rsync max'],
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
