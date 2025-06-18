<?php

$name = 'suricata';
$unit_text = 'flows/s';
$descr = 'DHCP';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dhcp']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dhcp']);
}

require 'includes/html/graphs/generic_stats.inc.php';
