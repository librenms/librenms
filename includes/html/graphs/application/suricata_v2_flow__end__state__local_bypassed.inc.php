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
    $flow__end__state__local_bypassed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__state__local_bypassed']);
} else {
    $flow__end__state__local_bypassed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__state__local_bypassed']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__end__state__local_bypassed_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__end__state__local_bypassed_rrd_filename,
        'descr' => 'End Local Bypass',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow__end__state__local_bypassed_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
