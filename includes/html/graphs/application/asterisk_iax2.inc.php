<?php

require 'includes/html/graphs/common.inc.php';
$i = 0;
$scale_min = 0;
$nototal = 1;
$unit_text = 'IAX2 Peers';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'asterisk', 'iax2', $app->app_id]);

$astiax2_access_array = [
    'iax2peers' => 'Total Peers',
    'iax2online' => 'Online',
    'iax2offline' => 'Offline',
    'iax2unmonitored' => 'Unmonitored',
];

$colours = 'mixed';
$rrd_list = [];

foreach ($astiax2_access_array as $ds => $descr) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $ds;
    $i++;
}

require 'includes/html/graphs/generic_multi_line.inc.php';
