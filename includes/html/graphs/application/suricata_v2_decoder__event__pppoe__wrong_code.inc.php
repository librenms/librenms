<?php

$name = 'suricata';
$unit_text = 'PPPoE pkts/s';
$descr = 'Wrong Code';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__pppoe__wrong_code']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__pppoe__wrong_code']);
}

require 'includes/html/graphs/generic_stats.inc.php';
