<?php

$name = 'nextcloud';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'free']);
$used_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'used']);
$total_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'total']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'free',
        'ds' => 'data',
    ],
    [
        'filename' => $used_rrd_filename,
        'descr' => 'used',
        'ds' => 'data',
    ],
    [
        'filename' => $total_rrd_filename,
        'descr' => 'total',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
