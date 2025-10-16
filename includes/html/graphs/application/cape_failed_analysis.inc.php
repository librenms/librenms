<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Run Count';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);
$descr = 'failed_analysis';
$ds = 'failed_analysis';

require 'includes/html/graphs/generic_stats.inc.php';
