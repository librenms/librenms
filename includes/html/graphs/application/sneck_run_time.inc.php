<?php

$name = 'sneck';
$unit_text = 'Seconds';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'run_time']);

$rrd_list = [
];

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Run Time',
        'ds' => 'data',
    ];
}

require 'includes/html/graphs/generic_multi_line.inc.php';
