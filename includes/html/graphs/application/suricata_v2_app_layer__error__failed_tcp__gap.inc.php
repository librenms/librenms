<?php

$name = 'suricata';
$unit_text = 'errors/s';
$descr = 'Failed TCP';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_tcp__gap']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_tcp__gap']);
}

require 'includes/html/graphs/generic_stats.inc.php';
