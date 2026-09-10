<?php

$name = 'suricata';
$unit_text = 'IPv4 pkts/s';
$descr = 'Opt Malformed';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_malformed']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_malformed']);
}

require 'includes/html/graphs/generic_stats.inc.php';
