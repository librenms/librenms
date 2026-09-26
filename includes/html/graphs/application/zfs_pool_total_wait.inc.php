<?php

$name = 'zfs';
$unit_text = 'ms';
$divider = 1000000;
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____total_wait_r']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Read',
        'ds' => 'data',
    ],
    [
        'filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____total_wait_w']),
        'descr' => 'Write',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
