<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 16;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$flow__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__memuse']);
$ftp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___ftp__memuse']);
$http__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
$tcp__memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__memuse']);
$tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__reassembly_memuse']);

if (Rrd::checkRrdExists($flow__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__memuse_rrd_filename,
        'descr' => 'Flow Memuse',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($ftp__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $ftp__memuse_rrd_filename,
        'descr' => 'FTP Memuse',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($http__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $http__memuse_rrd_filename,
        'descr' => 'HTTP Memuse',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($tcp__memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__memuse_rrd_filename,
        'descr' => 'TCP Memuse',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($tcp__reassembly_memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__reassembly_memuse_rrd_filename,
        'descr' => 'TCP Reass Memuse',
        'ds' => 'data',
    ];
}
if (! isset($rrd_file[0]) && Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Flow',
        'ds' => 'f_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'ftp_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'http_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TCP',
        'ds' => 'tcp_memuse',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TCP Reass',
        'ds' => 'tcp_reass_memuse',
    ];
}
if (! isset($rrd_file[0])) {
    d_echo('No RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
