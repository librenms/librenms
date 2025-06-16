<?php

$name = 'php-fpm';

$descr = 'Slow Requests';
$stat = 'slow_requests';
$unit_text = 'Requests/S';
$ds = 'data';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___slow_requests']);

if (! Rrd::checkRrdExists($rrd_filename)) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $ds = 'sr';
}

require 'includes/html/graphs/generic_stats.inc.php';
