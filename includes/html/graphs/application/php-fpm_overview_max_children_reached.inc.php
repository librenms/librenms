<?php

$name = 'php-fpm';

$descr = 'Max Chldrn Reached';
$unit_text = 'Per Second';
$ds = 'data';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___max_children_reached']);

if (! Rrd::checkRrdExists($rrd_filename)) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $ds = 'mcr';
}

require 'includes/html/graphs/generic_stats.inc.php';
