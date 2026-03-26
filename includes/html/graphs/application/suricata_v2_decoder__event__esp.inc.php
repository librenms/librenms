<?php

$name = 'suricata';
$unit_text = 'ESP packets/s';
$descr = 'Pkgs To Small';
$descr_len = 20;
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__esp__pkt_too_small']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__esp__pkt_too_small']);
}

require 'includes/html/graphs/generic_stats.inc.php';
