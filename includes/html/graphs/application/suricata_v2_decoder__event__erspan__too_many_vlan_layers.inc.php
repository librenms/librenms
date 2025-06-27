<?php

$name = 'suricata';
$unit_text = 'ERSPAN pkts/s';
$descr = 'Too Many VLAN Layers';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__erspan__too_many_vlan_layers']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__erspan__too_many_vlan_layers']);
}

require 'includes/html/graphs/generic_stats.inc.php';
