<?php

$name = 'suricata';
$unit_text = 'TCP pkts/s';
$descr = 'Opt Duplicate';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__tcp__opt_duplicate']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__tcp__opt_duplicate']);
}

require 'includes/html/graphs/generic_stats.inc.php';
