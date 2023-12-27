<?php

$name = 'hv-monitor';
$unit_text = 'VM Statuses';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'On',
        'ds' => 'on',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Off',
        'ds' => 'off',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Off, Hard',
        'ds' => 'off_hard',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Off, Soft',
        'ds' => 'off_soft',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Unknown',
        'ds' => 'unknown',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Paused',
        'ds' => 'paused',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Crashed',
        'ds' => 'crashed',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Blocked',
        'ds' => 'blocked',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'No State',
        'ds' => 'nostate',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'PM Suspended',
        'ds' => 'pmsuspended',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
