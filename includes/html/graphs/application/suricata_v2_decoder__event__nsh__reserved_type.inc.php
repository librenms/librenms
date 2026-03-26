<?php

$name = 'suricata';
$unit_text = 'NSH pkts/s';
$descr = 'Res Type';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__reserved_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__reserved_type']);
}

require 'includes/html/graphs/generic_stats.inc.php';
