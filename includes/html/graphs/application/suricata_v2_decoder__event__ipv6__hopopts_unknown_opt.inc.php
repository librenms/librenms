<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'Hop Opts Unknown Opt';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__hopopts_unknown_opt']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__hopopts_unknown_opt']);
}

require 'includes/html/graphs/generic_stats.inc.php';
