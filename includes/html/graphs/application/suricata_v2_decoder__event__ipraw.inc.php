<?php

$name = 'suricata';
$unit_text = 'IP Raw packets/s';
$descr = 'Inv IP Ver';
$descr_len = 20;
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipraw__invalid_ip_version']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipraw__invalid_ip_version']);
}

require 'includes/html/graphs/generic_stats.inc.php';
