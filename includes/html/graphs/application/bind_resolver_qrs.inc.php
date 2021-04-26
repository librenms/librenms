<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'quiries/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'resolver']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'IPv4 Sent',
        'ds'       => 'i4qs',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'IPv6 Sent',
        'ds'       => 'i6qs',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'IPv4 Rcvd',
        'ds'       => 'i4rr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'IPv6 Rcvd',
        'ds'       => 'i6rr',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
