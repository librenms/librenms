<?php

$name = 'zfs';
$unit_text = 'errors';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____checksum_errors']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Checksum',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____read_errors']),
        'descr' => 'Read',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool'] . '____write_errors']),
        'descr' => 'Write',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
