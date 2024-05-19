<?php

$name = 'suricata';
$unit_text = 'bytes/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$descr_len = 20;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$decoder__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__bytes']);
$flow_bypassed__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__bytes']);
$flow_bypassed__local_bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__local_bytes']);
$flow_bypassed__local_capture_bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__local_capture_bytes']);

$rrd_list = [];
if (Rrd::checkRrdExists($decoder__bytes_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__bytes_rrd_filename,
        'descr' => 'Decoder',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow_bypassed__bytes_rrd_filename,
        'descr' => 'Flow Bypassed',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow_bypassed__local_bytes_rrd_filename,
        'descr' => 'Flow Loc Bypassed',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow_bypassed__local_capture_bytes_rrd_filename,
        'descr' => 'Flow Loc Byp Cap',
        'ds' => 'data',
    ];
} elseif (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Bytes',
        'ds' => 'bytes',
    ];
} else {
    d_echo('RRD "' . $flow_bypassed__bytes_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
