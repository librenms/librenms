<?php

$name = 'suricata';
$unit_text = 'VLAN pkts/s';
$descr = 'Too Many Layers';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__vlan__too_many_layers']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__vlan__too_many_layers']);
}

require 'includes/html/graphs/generic_stats.inc.php';
