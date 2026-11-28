<?php

$name = 'suricata';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$drop_percent_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___drop_percent']);

$rrd_list = [];
if (Rrd::checkRrdExists($drop_percent_rrd_filename)) {
    $unit_text = '% of packets';
    $colours = 'psychedelic';
    $dostack = 0;
    $printtotal = 0;
    $addarea = 0;
    $transparency = 15;
    // drop_percent is stored as a fraction, 0.01 being 1%
    $multiplier = 100;

    $rrd_list[] = [
        'filename' => $drop_percent_rrd_filename,
        'descr' => 'Max Drop',
        'ds' => 'data',
    ];

    require 'includes/html/graphs/generic_multi_line.inc.php';
} elseif (Rrd::checkRrdExists($rrd_filename)) {
    $unit_text = '% Of Packets';
    $colours = 'psychedelic';
    $dostack = 0;
    $printtotal = 0;
    $addarea = 0;
    $transparency = 15;

    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dropped',
        'ds' => 'drop_percent',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'If Dropped',
        'ds' => 'ifdrop_percent',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Error',
        'ds' => 'error_percent',
    ];

    require 'includes/html/graphs/generic_multi_line.inc.php';
}
