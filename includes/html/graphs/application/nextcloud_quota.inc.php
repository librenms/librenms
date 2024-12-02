<?php

$name = 'nextcloud';
$unit_text = 'bytes';
$descr = 'storage quota';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___quota']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'quota']);
}

if (Rrd::checkRrdExists($uptime_rrd_filename)) {
    $ds = 'data';
    $filename = $rrd_filename;
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
