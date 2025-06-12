<?php

$name = 'nextcloud';
$unit_text = 'bytes';
$descr = 'storage quota';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___quota']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'quota']);
}

$ds = 'data';
$filename = $rrd_filename;


require 'includes/html/graphs/generic_stats.inc.php';
