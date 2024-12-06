<?php

$name = 'suricata';
$unit_text = 'bytes/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $decoder__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__bytes']);
    $flow_bypassed__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow_bypassed__bytes']);
} else {
    $decoder__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__bytes']);
    $flow_bypassed__bytes_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow_bypassed__bytes']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($decoder__bytes_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__bytes_rrd_filename,
        'descr' => 'Packets',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($flow_bypassed__bytes_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow_bypassed__bytes_rrd_filename,
        'descr' => 'Eth Pkts',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $flow_bypassed__bytes_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
