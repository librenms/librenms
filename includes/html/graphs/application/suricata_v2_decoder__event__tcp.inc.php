<?php

$name = 'suricata';
$unit_text = 'TCP pkt/s';
$colours = 'psychedelic';
$descr_len = 16;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__hlen_too_small']);
    $decoder__event__tcp__invalid_optlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__invalid_optlen']);
    $decoder__event__tcp__opt_duplicate_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__opt_duplicate']);
    $decoder__event__tcp__opt_invalid_len_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__opt_invalid_len']);
    $decoder__event__tcp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__pkt_too_small']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__hlen_too_small']);
    $decoder__event__tcp__invalid_optlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__invalid_optlen']);
    $decoder__event__tcp__opt_duplicate_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__opt_duplicate']);
    $decoder__event__tcp__opt_invalid_len_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__opt_invalid_len']);
    $decoder__event__tcp__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__pkt_too_small']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Hlen too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__tcp__invalid_optlen_rrd_filename,
        'descr' => 'Invalid Opt Len',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__tcp__opt_duplicate_rrd_filename,
        'descr' => 'Opt Dup',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__tcp__invalid_optlen_rrd_filename,
        'descr' => 'Invalid Opt Len',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__tcp__opt_invalid_len_rrd_filename,
        'descr' => 'Opt Invalid Len',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__tcp__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
