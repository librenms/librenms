<?php

$munge = true;
$name = 'nextcloud';
$unit_text = 'days ago';
$descr = 'last seen';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___last_seen']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'last_seen']);
}

require 'includes/html/graphs/generic_stats.inc.php';
