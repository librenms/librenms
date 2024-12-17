<?php

$name = 'suricata';
$unit_text = 'flows/s';
$descr = 'IKE';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ike']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ike']);
}

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
