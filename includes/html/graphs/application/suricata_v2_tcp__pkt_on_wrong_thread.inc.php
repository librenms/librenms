<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $tcp__pkt_on_wrong_thread_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__pkt_on_wrong_thread']);
} else {
    $tcp__pkt_on_wrong_thread_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__pkt_on_wrong_thread_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__pkt_on_wrong_thread_rrd_filename,
        'descr' => 'TCP Wrong Thread',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__pkt_on_wrong_thread_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
