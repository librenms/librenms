<?php

$name = 'suricata';
$unit_text = 'pkt/s';
$descr = 'NFS UDP';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__nfs_udp']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__nfs_udp']);
}

require 'includes/html/graphs/generic_stats.inc.php';
