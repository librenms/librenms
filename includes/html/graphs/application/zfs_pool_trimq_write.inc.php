<?php

$name = 'zfs';
$unit_text = 'ops';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____trimq_write_a']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Active',
        'ds' => 'data',
    ],
    [
        'filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____trimq_write_p']),
        'descr' => 'Pending',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
