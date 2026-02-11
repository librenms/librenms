<?php

$name = 'suricata';
$unit_text = 'UDP pkt/s';
$colours = 'psychedelic';
$descr_len = 14;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__hlen_invalid']);
    $decoder__event__udp__hlen_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__hlen_too_small']);
    $decoder__event__udp__len_invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__len_invalid']);
    $decoder__event__udp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__pkt_too_small']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__hlen_invalid']);
    $decoder__event__udp__hlen_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__hlen_too_small']);
    $decoder__event__udp__len_invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__len_invalid']);
    $decoder__event__udp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__pkt_too_small']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Hlen Invalid',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__udp__hlen_too_small_rrd_filename,
        'descr' => 'Hlen Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__udp__len_invalid_rrd_filename,
        'descr' => 'Len Invalid',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__udp__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
