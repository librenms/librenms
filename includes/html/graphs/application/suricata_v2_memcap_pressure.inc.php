<?php

$name = 'suricata';
$unit_text = 'Mem Pressure';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_prefix = isset($vars['sinstance']) ? 'instance_' . $vars['sinstance'] . '___' : 'totals___';

// Suricata 8.0 renamed memcap_pressure to memcap.pressure
$memcap_pressure_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap__pressure']);
$memcap_pressure_max_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap__pressure_max']);
if (! Rrd::checkRrdExists($memcap_pressure_rrd_filename)) {
    $memcap_pressure_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap_pressure']);
    $memcap_pressure_max_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap_pressure_max']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($memcap_pressure_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $memcap_pressure_rrd_filename,
        'descr' => 'Current',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $memcap_pressure_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($memcap_pressure_max_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $memcap_pressure_max_rrd_filename,
        'descr' => 'Max',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $memcap_pressure_max_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
