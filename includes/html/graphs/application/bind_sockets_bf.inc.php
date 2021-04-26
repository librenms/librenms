<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'failures / sec.';
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
        'descr'    => 'UDP/IPv4',
        'ds'       => 'ui4sbf',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UDP/IPv6',
        'ds'       => 'ui6sbf',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv4',
        'ds'       => 'ti4sbf',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TCP/IPv6',
        'ds'       => 'ti6sbf',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
