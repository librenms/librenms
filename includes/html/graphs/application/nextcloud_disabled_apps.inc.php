<?php

$name = 'nextcloud';
$unit_text = 'apps';
$descr = 'disabled apps';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___disabled_apps']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'disabled_apps']);
}

require 'includes/html/graphs/generic_stats.inc.php';
