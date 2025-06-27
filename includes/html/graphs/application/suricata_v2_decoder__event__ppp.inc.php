<?php

$name = 'suricata';
$unit_text = 'PPP pkt/s';
$colours = 'psychedelic';
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__ip4_pkt_too_small']);
    $decoder__event__ppp__ip6_pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__ip6_pkt_too_small']);
    $decoder__event__ppp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__pkt_too_small']);
    $decoder__event__ppp__unsup_proto_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__unsup_proto']);
    $decoder__event__ppp__vju_pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__vju_pkt_too_small']);
    $decoder__event__ppp__wrong_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ppp__wrong_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__ip4_pkt_too_small']);
    $decoder__event__ppp__ip6_pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__ip6_pkt_too_small']);
    $decoder__event__ppp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__pkt_too_small']);
    $decoder__event__ppp__unsup_proto_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__unsup_proto']);
    $decoder__event__ppp__vju_pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__vju_pkt_too_small']);
    $decoder__event__ppp__wrong_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ppp__wrong_type']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv4 Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ppp__ip6_pkt_too_small_rrd_filename,
        'descr' => 'IPv6 Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ppp__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ppp__unsup_proto_rrd_filename,
        'descr' => 'Unsup Proto',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ppp__vju_pkt_too_small_rrd_filename,
        'descr' => 'Vju Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ppp__wrong_type_rrd_filename,
        'descr' => 'Wrong Type',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
