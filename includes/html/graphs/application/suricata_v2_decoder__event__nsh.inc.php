<?php

$name = 'suricata';
$unit_text = 'NSH pkt/s';
$colours = 'psychedelic';
$descr_len = 13;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__bad_header_length']);
    $decoder__event__nsh__header_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__header_too_small']);
    $decoder__event__nsh__reserved_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__reserved_type']);
    $decoder__event__nsh__unknown_payload_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__unknown_payload']);
    $decoder__event__nsh__unsupported_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__unsupported_type']);
    $decoder__event__nsh__unsupported_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__unsupported_version']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__bad_header_length']);
    $decoder__event__nsh__header_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__header_too_small']);
    $decoder__event__nsh__reserved_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__reserved_type']);
    $decoder__event__nsh__unknown_payload_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__unknown_payload']);
    $decoder__event__nsh__unsupported_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__unsupported_type']);
    $decoder__event__nsh__unsupported_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__unsupported_version']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Bad Hdr Len',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__nsh__header_too_small_rrd_filename,
        'descr' => 'Hdr Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__nsh__reserved_type_rrd_filename,
        'descr' => 'Reserved Type',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__nsh__unknown_payload_rrd_filename,
        'descr' => 'Unknown Payload',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__nsh__unsupported_type_rrd_filename,
        'descr' => 'Unsup Type',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__nsh__unsupported_version_rrd_filename,
        'descr' => 'Unsup Ver',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
