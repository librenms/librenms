<?php

$name = 'bind';
$unit_text = 'Heap Memory';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'cache']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Total',
        'ds' => 'chmt',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'In Use',
        'ds' => 'chmiu',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Highest In Use',
        'ds' => 'chhmiu',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
