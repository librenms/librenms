<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Seconds PPM';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'chronyd', $app->app_id]);
$array = [
    'frequency' => ['descr' => 'Error rate'],
    'residual_frequency' => ['descr' => 'Ref clk offset'],
    'skew' => ['descr' => 'Sys clk skew'],
];

$i = 0;
foreach ($array as $ds => $var) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $var['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$i");
    $i++;
}

require 'includes/html/graphs/generic_multi_line.inc.php';
