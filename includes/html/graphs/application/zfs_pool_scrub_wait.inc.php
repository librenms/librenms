<?php

$name = 'zfs';
$unit_text = 'ms';
$divider = 1000000;
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____scrub_wait']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Scrub',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
