<?php

$name = 'hv-monitor';
$unit_text = 'Seconds';
$colours = 'psychedelic';
$dostack = 1;
$printtotal = 1;
$addarea = 1;
$transparency = 15;

if (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'User',
        'ds' => 'usertime',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Sys',
        'ds' => 'systime',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
