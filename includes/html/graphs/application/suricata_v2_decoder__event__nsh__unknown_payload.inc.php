<?php

$name = 'suricata';
$unit_text = 'NSH pkts/s';
$descr = 'Unknown Payload';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__unknown_payload']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__unknown_payload']);
}

require 'includes/html/graphs/generic_stats.inc.php';
