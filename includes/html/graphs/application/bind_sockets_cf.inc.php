<?php

$name = 'bind';
$unit_text = 'failures / sec.';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'sockets']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv4',
        'ds' => 'ui4scf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv6',
        'ds' => 'ui6scf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv4',
        'ds' => 'ti4scf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv6',
        'ds' => 'ti6scf',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
