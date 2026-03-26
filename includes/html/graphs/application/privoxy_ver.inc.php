<?php

$name = 'privoxy';
$unit_text = 'HTTP Versions';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => '1.0',
        'ds' => 'ver_1_0',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '1.1',
        'ds' => 'ver_1_1',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '2',
        'ds' => 'ver_2',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '3',
        'ds' => 'ver_3',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
