<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dropped',
        'ds' => 'dropped',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'If Dropped',
        'ds' => 'ifdropped',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Errors',
        'ds' => 'errors',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dec Inv.',
        'ds' => 'dec_invalid',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Too Many Layers',
        'ds' => 'dec_too_many_layer',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
