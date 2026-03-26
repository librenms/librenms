<?php

$name = 'suricata';
$unit_text = 'errors/s';
$descr = 'ModBus';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__modbus__alloc']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__alloc']);
}

require 'includes/html/graphs/generic_stats.inc.php';
