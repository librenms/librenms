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
    $tcp__ack_unseen_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__ack_unseen_data']);
} else {
    $tcp__ack_unseen_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__ack_unseen_data']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__ack_unseen_data_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__ack_unseen_data_rrd_filename,
        'descr' => 'TCP Ack Unseen',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__ack_unseen_data_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
