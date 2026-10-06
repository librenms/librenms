<?php

$name = 'suricata';
$unit_text = 'bytes/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $flow_bypassed__local_bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow_bypassed__local_bytes']);
} else {
    $flow_bypassed__local_bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__local_bytes']);
}

$rrd_list = [];
$rrd_list[] = [
    'filename' => $flow_bypassed__local_bytes_rrd_filename,
    'descr' => 'Bypass Local',
    'ds' => 'data',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
