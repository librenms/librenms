<?php

$name = 'suricata';
$unit_text = 'bytes/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 15;

if (isset($vars['sinstance'])) {
    $flow_bypassed__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow_bypassed__bytes']);
} else {
    $flow_bypassed__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__bytes']);
}

$rrd_list = [];
$rrd_list[] = [
    'filename' => $flow_bypassed__bytes_rrd_filename,
    'descr' => 'Flow Bypassed',
    'ds' => 'data',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
