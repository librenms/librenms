<?php

$name = 'php-fpm';

$descr = 'Slow Requests';
$stat = 'slow_requests';
$unit_text = 'Requests/S';
$ds = 'data';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___slow_requests']);

if (! Rrd::checkRrdExists($filename)) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $ds = 'sr';
    if (! Rrd::checkRrdExists($filename)) {
        echo 'file missing: ' . $filename;
    }
}

require 'includes/html/graphs/generic_stats.inc.php';
