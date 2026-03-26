<?php

$name = 'suricata';
$unit_text = 'Ethernet pkts/s';
$descr = 'Pkt To Small';
$descr_len = 20;
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ethernet__pkt_too_small']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ethernet__pkt_too_small']);
}

require 'includes/html/graphs/generic_stats.inc.php';
