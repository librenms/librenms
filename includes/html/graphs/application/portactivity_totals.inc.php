<?php

$name = 'portactivity';
$unit_text = 'Connections';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['port']]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Total',
        'ds' => 'total_conns',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'From',
        'ds' => 'total_from',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'To',
        'ds' => 'total_to',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
