<?php

$name = 'suricata';
$unit_text = 'PPPoE pkt/s';
$colours = 'psychedelic';
$descr_len = 14;

if (isset($vars['sinstance'])) {
    $decoder__event__pppoe__malformed_tags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__malformed_tags']);
    $decoder__event__pppoe__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__pkt_too_small']);
    $decoder__event__pppoe__wrong_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__wrong_code']);
} else {
    $decoder__event__pppoe__malformed_tags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__malformed_tags']);
    $decoder__event__pppoe__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__pkt_too_small']);
    $decoder__event__pppoe__wrong_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__wrong_code']);
}

if (Rrd::checkRrdExists($decoder__event__pppoe__malformed_tags_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__event__pppoe__malformed_tags_rrd_filename,
        'descr' => 'Malformed Tags',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__pppoe__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__pppoe__wrong_code_rrd_filename,
        'descr' => 'Wrong Code',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $decoder__event__pppoe__malformed_tags_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
