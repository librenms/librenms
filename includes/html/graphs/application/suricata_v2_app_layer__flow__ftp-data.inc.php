<?php

$name = 'suricata';
$unit_text = 'flows/s';
$descr = 'FTP-Data';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ftp-data']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ftp-data']);
}

require 'includes/html/graphs/generic_stats.inc.php';
