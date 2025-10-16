<?php

$name = 'suricata';
$unit_text = 'ERSPAN pkts/s';
$descr = 'Unsup Ver';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__erspan__unsupported_version']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__erspan__unsupported_version']);
}

require 'includes/html/graphs/generic_stats.inc.php';
