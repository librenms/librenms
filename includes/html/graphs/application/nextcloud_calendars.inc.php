<?php

$name = 'nextcloud';
$unit_text = 'calendars';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___calendars']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'calendars']);
}

require 'includes/html/graphs/generic_stats.inc.php';
