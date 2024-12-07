<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 16;

if (isset($vars['sinstance'])) {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__memuse']);
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___ftp__memuse']);
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memuse']);
    $tcp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__memuse']);
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__reassembly_memuse']);
} else {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__memuse']);
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___ftp__memuse']);
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
    $tcp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__memuse']);
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__reassembly_memuse']);
}

if (Rrd::checkRrdExists($flow__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__memuse_rrd_filename,
        'descr' => 'Flow Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($ftp__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $ftp__memuse_rrd_filename,
        'descr' => 'FTP Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $ftp__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($http__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $http__memuse_rrd_filename,
        'descr' => 'HTTP Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $http__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($tcp__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__memuse_rrd_filename,
        'descr' => 'TCP Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__memuse_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($tcp__reassembly_memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__reassembly_memuse_rrd_filename,
        'descr' => 'TCP Reass Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__reassembly_memuse_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
