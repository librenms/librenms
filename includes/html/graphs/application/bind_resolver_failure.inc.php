<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'per second';
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
        'descr'    => 'NXDOMAIN',
        'ds'       => 'nr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SERVFAIL',
        'ds'       => 'sr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FORMERR',
        'ds'       => 'fr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'REFUSED',
        'ds'       => 'rr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'EDNS(0) qry fl',
        'ds'       => 'eqf',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Trnctd Rcvd',
        'ds'       => 'trr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Retry',
        'ds'       => 'qr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Timeout',
        'ds'       => 'qt',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Lame Dele.',
        'ds'       => 'ldr',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
