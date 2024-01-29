<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] .'___capture__kernel_packets' ]);
} else {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id], 'totals___capture__kernel_packets');
}

if (isset($vars['sinstance'])) {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_packets' ]);
} else {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id], 'totals___capture__kernel_packets');
}

if (isset($vars['sinstance'])) {
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_drops' ]);
} else {
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id], 'totals___capture__kernel_drops');
}

if (isset($vars['sinstance'])) {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_ifdrops' ]);
} else {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id], 'totals___capture__kernel_ifdrops');
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $capture__kernel_packets_rrd_filename,
        'descr' => 'Packets',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $capture__kernel_packets_rrd_filename,
        'descr' => 'Eth Pkts',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $capture__kernel_drops_rrd_filename,
        'descr' => 'Drops',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $capture__kernel_ifdrops_rrd_filename,
        'descr' => 'If Dropped',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
