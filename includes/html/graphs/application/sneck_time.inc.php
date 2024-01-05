<?php

$name = 'sneck';
$unit_text = 'Diff In Secs';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'On Dev',
        'ds' => 'time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'To Polling',
        'ds' => 'time_to_polling',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
