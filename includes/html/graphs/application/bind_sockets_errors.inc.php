<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'errors / sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'sockets']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDP/IPv4 send',
        'ds'       => 'ui4se',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDP/IPv6 send',
        'ds'       => 'ui6se',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv4 send',
        'ds'       => 'ti4se',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv6 send',
        'ds'       => 'ti6se',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDP/IPv4 recv',
        'ds'       => 'ui4re',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDP/IPv6 recv',
        'ds'       => 'ui6re',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv4 recv',
        'ds'       => 'ti4re',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv6 recv',
        'ds'       => 'ti6re',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
