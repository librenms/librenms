<?php

$name = 'suricata';
$unit_text = 'ERSPAN pkt/s';
$colours = 'psychedelic';
$descr_len = 19;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__erspan__header_too_small']);
    $decoder__event__erspan__too_many_vlan_layers_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__erspan__too_many_vlan_layers']);
    $decoder__event__erspan__unsupported_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__erspan__unsupported_version']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__erspan__header_too_small']);
    $decoder__event__erspan__too_many_vlan_layers_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__erspan__too_many_vlan_layers']);
    $decoder__event__erspan__unsupported_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__erspan__unsupported_version']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Header Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__erspan__too_many_vlan_layers_rrd_filename,
        'descr' => 'Too Many VLAN Layers',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__erspan__unsupported_version_rrd_filename,
        'descr' => 'Unsupported Ver',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
