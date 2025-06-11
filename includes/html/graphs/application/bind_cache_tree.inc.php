<?php

$name = 'bind';
$unit_text = 'Tree Memory';
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
        'ds' => 'ctmt',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'In Use',
        'ds' => 'ctmiu',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Highest In Use',
        'ds' => 'cthmiu',
    ]
];

require 'includes/html/graphs/generic_multi_line.inc.php';
