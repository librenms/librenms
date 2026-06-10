<?php

$name = 'nextcloud';
$unit_text = 'users';
$descr = 'user count';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___user_count']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'user_count']);
}

require 'includes/html/graphs/generic_stats.inc.php';
