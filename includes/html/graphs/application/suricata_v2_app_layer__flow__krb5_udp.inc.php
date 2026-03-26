<?php

$name = 'suricata';
$unit_text = 'flows/s';
$descr = 'KRB5 UDP';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__krb5_udp']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__krb5_udp']);
}

require 'includes/html/graphs/generic_stats.inc.php';
