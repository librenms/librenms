<?php

$name = 'suricata';
$unit_text = 'GRE pkts/s';
$descr = 'Ver0 Flags';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version0_flags']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version0_flags']);
}

require 'includes/html/graphs/generic_stats.inc.php';
