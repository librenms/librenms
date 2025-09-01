<?php

$name = 'nextcloud';
$unit_text = 'apps';
$descr = 'enabled apps';
$ds = 'data';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___enabled_apps']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'enabled_apps']);
}

require 'includes/html/graphs/generic_stats.inc.php';
