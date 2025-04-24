<?php

$name = 'php-fpm';

$descr = 'Max Chldrn Reached';
$unit_text = 'Per Second';
$ds = 'data';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___max_children_reached']);

if (! Rrd::checkRrdExists($filename)) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $ds = 'mcr';
    if (! Rrd::checkRrdExists($filename)) {
        echo 'file missing: ' . $filename;
    }
}

require 'includes/html/graphs/generic_stats.inc.php';
