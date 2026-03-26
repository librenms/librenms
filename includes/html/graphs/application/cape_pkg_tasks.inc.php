<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Count';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg___-___', $vars['package']]);
$descr = 'Tasks';
$ds = 'tasks';

require 'includes/html/graphs/generic_stats.inc.php';
