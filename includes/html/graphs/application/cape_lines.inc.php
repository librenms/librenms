<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Lines';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'debug',
        'ds' => 'debug',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'info',
        'ds' => 'info',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'warning',
        'ds' => 'warning',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'error',
        'ds' => 'error',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'critical',
        'ds' => 'critical',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
