<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Running Procs Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-running_processes___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'running_processes']);
}
$descr = 'Average';
$ds = 's3running_processes';

require 'includes/html/graphs/generic_stats.inc.php';
