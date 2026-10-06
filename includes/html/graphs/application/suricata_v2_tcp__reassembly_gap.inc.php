<?php

$name = 'suricata';
$unit_text = 'events/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $tcp__reassembly_gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__reassembly_gap']);
} else {
    $tcp__reassembly_gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__reassembly_gap']);
}

$rrd_list = [];
$rrd_list[] = [
    'filename' => $tcp__reassembly_gap_rrd_filename,
    'descr' => 'TCP Reass Gap',
    'ds' => 'data',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
