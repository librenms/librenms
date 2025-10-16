<?php

$name = 'nextcloud';
$unit_text = 'bytes';
$descr = 'used storage';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___used']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'used']);
}

require 'includes/html/graphs/generic_stats.inc.php';
