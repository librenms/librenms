<?php

$name = 'suricata';
$unit_text = 'PPPoE pkts/s';
$descr = 'Malformed Tags';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__malformed_tags']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__malformed_tags']);
}

require 'includes/html/graphs/generic_stats.inc.php';
