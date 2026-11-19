<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 16;

$rrd_prefix = isset($vars['sinstance']) ? 'instance_' . $vars['sinstance'] . '___' : 'totals___';

// capture__kernel_drops_any is the extend's sum of all unwanted drops, including
// IPS error and memcap drop reasons, and only exists for totals
$drop_stats = [
    'capture__kernel_drops_any' => 'All Unwanted',
    'capture__kernel_drops' => 'Kernel Drops',
    'capture__kernel_ifdrops' => 'Kernel IF Drops',
    'capture__dag_drops' => 'DAG Drops',
    'capture__dpdk__imissed' => 'DPDK Missed',
    'capture__dpdk__no_mbufs' => 'DPDK No Mbufs',
];

$rrd_list = [];
foreach ($drop_stats as $drop_stat => $drop_descr) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . $drop_stat]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $drop_descr,
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $rrd_filename . '" not found');
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
