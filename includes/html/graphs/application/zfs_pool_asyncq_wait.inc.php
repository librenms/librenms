<?php

$name = 'zfs';
$unit_text = 'ms';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____asyncq_wait_w']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Write',
        'ds' => 'data',
    ],
    [
        'filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____asyncq_wait_r']),
        'descr' => 'Read',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
