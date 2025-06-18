<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Dropped Files Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$vars['stddev'] = ($vars['stddev'] ?? 'off') === 'on' ? 'on' : 'off';

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-dropped_files___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'dropped_files']);
}

$descr = 'Average';
$ds = 's3dropped_files';

require 'includes/html/graphs/generic_stats.inc.php';
