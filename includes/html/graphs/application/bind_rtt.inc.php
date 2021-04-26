<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'RTT in ms/sec';
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
        'descr'    => '<10',
        'ds'       => 'rttl10',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => '10-100',
        'ds'       => 'rtt10t100',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => '100-500',
        'ds'       => 'rtt100t500',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => '500-800',
        'ds'       => 'rtt500t800',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => '800-1600',
        'ds'       => 'rtt800t1600',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => '>1600',
        'ds'       => 'rttg1600',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
