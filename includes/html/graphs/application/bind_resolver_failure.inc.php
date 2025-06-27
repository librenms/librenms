<?php

$name = 'bind';
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'resolver']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'NXDOMAIN',
        'ds' => 'nr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SERVFAIL',
        'ds' => 'sr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FORMERR',
        'ds' => 'fr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'REFUSED',
        'ds' => 'rr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'EDNS(0) qry fl',
        'ds' => 'eqf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Trnctd Rcvd',
        'ds' => 'trr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Retry',
        'ds' => 'qr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Timeout',
        'ds' => 'qt',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Lame Dele.',
        'ds' => 'ldr',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
