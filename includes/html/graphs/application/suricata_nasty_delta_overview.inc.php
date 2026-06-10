<?php

$name = 'suricata';
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_ifdrops']);
$capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_drops']);
$error_delta_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___error_delta']);

$rrd_list = [];
if (Rrd::checkRrdExists($capture__kernel_ifdrops_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $capture__kernel_ifdrops_rrd_filename,
        'descr' => 'If Drops',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($capture__kernel_drops_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $capture__kernel_drops_rrd_filename,
        'descr' => 'Drops',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($error_delta_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $error_delta_rrd_filename,
        'descr' => 'Errors',
        'ds' => 'data',
    ];
}
if (! isset($rrd_list[0]) && Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Packets',
        'ds' => 'packets',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dec. Packets',
        'ds' => 'dec_packets',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dropped',
        'ds' => 'dropped',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'If Dropped',
        'ds' => 'ifdropped',
    ];
}
if (! isset($rrd_list[0])) {
    d_echo('No RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
