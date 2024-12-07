<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Malscore Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-malscore___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'malscore']);
}

if (Rrd::checkRrdExists($rrd_filename)) {
    $filename = $rrd_filename;
    $descr = 'Mean';
    $ds = 's3malscore';
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
