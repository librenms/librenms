<?php

$name = 'suricata';
$unit_text = 'PPPoE pkt/s';
$colours = 'psychedelic';
$descr_len = 14;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__malformed_tags']);
    $decoder__event__pppoe__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__pkt_too_small']);
    $decoder__event__pppoe__wrong_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__wrong_code']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__malformed_tags']);
    $decoder__event__pppoe__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__pkt_too_small']);
    $decoder__event__pppoe__wrong_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__wrong_code']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Malformed Tags',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__pppoe__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__pppoe__wrong_code_rrd_filename,
        'descr' => 'Wrong Code',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
