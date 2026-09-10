<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds = 'stratum';
$colour_area = 'FFCECE';
$colour_line = '880000';
$colour_area_max = 'FFCCCC';
$graph_max = 0;
$unit_text = 'Stratum';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app->app_id]);

require 'includes/html/graphs/generic_simplex.inc.php';
