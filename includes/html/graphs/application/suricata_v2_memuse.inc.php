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
    $_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___']);
} else {
    $_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___']);
}

if (isset($vars['sinstance'])) {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__memuse']);
} else {
    $flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__memuse']);
}
if (isset($vars['sinstance'])) {
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___ftp__memuse']);
} else {
    $ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___ftp__memuse']);
}
if (isset($vars['sinstance'])) {
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memuse']);
} else {
    $http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
}
if (isset($vars['sinstance'])) {
    $tcp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__memuse']);
} else {
    $tcp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__memuse']);
}
if (isset($vars['sinstance'])) {
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__reassembly_memuse']);
} else {
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__reassembly_memuse']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $_rrd_filename,
        'descr' => '',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $_rrd_filename . '" not found');
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
