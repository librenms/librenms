<?php

$name = 'suricata';
$unit_text = 'flows/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 19;

if (isset($vars['sinstance'])) {
    $flow_bypassed__closed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow_bypassed__closed']);
} else {
    $flow_bypassed__closed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__closed']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow_bypassed__closed_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow_bypassed__closed_rrd_filename,
        'descr' => 'Bypassed Closed',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow_bypassed__closed_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
