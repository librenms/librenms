<?php

$name = 'bind';
$unit_text = 'In Hash Table';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'adb']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Addresses',
        'ds' => 'aiht',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Names',
        'ds' => 'niht',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
