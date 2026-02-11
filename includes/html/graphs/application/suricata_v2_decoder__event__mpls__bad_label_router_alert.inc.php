<?php

$name = 'suricata';
$unit_text = 'MPLS pkts/s';
$descr = 'Bad Label Rtr Alt';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__mpls__bad_label_router_alert']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__mpls__bad_label_router_alert']);
}

require 'includes/html/graphs/generic_stats.inc.php';
