<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds = 'uptime';
$colour_area = \App\Facades\LibrenmsConfig::get('graph_colours.purples.0') . '33';
$colour_line = \App\Facades\LibrenmsConfig::get('graph_colours.purples.0');
$colour_area_max = 'FFEE99';
$graph_max = 0;
$unit_text = 'Seconds';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app->app_id]);

require 'includes/html/graphs/generic_simplex.inc.php';
