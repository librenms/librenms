<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Queries';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'adguard', $app->app_id]);
$array = [
    'queries' => ['descr' => 'DNS queries'],
    'blocked' => ['descr' => 'Blocked'],
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
