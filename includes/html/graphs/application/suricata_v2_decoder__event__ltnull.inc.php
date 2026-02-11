<?php

$name = 'suricata';
$unit_text = 'LT Null pkt/s';
$colours = 'psychedelic';
$descr_len = 14;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ltnull__pkt_too_small']);
    $decoder__event__ltnull__unsupported_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ltnull__unsupported_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ltnull__pkt_too_small']);
    $decoder__event__ltnull__unsupported_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ltnull__unsupported_type']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ltnull__unsupported_type_rrd_filename,
        'descr' => 'Unsup Type',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
