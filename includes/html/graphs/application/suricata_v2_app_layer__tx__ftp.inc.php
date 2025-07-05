<?php

$name = 'suricata';
$unit_text = 'pkt/s';
$descr = 'FTP';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ftp']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ftp']);
}

require 'includes/html/graphs/generic_stats.inc.php';
