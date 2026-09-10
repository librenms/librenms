<?php

$munge = true;
$name = 'suricata';
$unit_text = 'days';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___uptime']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___uptime']);
}

require 'includes/html/graphs/generic_stats.inc.php';
