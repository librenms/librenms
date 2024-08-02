<?php

$name = 'suricata';
$unit_text = 'events/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 20;

if (isset($vars['sinstance'])) {
    $tcp__stream_depth_reached_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__stream_depth_reached']);
} else {
    $tcp__stream_depth_reached_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__stream_depth_reached']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__stream_depth_reached_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__stream_depth_reached_rrd_filename,
        'descr' => 'TCP Strm Depth Rchd',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__stream_depth_reached_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
