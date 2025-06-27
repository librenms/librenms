<?php

$name = 'nextcloud';
$unit_text = 'percent';
$descr = 'relative storage';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___relative']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'relative']);
}

require 'includes/html/graphs/generic_stats.inc.php';
