<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_prefix = isset($vars['sinstance']) ? 'instance_' . $vars['sinstance'] . '___' : 'totals___';

// capture__packets_any covers every capture method, but only exists for totals
// from extend 1.1.0 onwards
$packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'capture__packets_any']);
if (! Rrd::checkRrdExists($packets_rrd_filename)) {
    $packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'capture__kernel_packets']);
}

$graph_stats = [
    ['filename' => $packets_rrd_filename, 'descr' => 'Packets'],
    ['filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'decoder__ethernet']), 'descr' => 'Eth Pkts'],
    ['filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'capture__kernel_drops']), 'descr' => 'Drops'],
    ['filename' => Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'capture__kernel_ifdrops']), 'descr' => 'If Dropped'],
];

$rrd_list = [];
foreach ($graph_stats as $graph_stat) {
    if (Rrd::checkRrdExists($graph_stat['filename'])) {
        $rrd_list[] = [
            'filename' => $graph_stat['filename'],
            'descr' => $graph_stat['descr'],
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $graph_stat['filename'] . '" not found');
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
