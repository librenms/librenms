<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds = 'calls';
$colour_area = '9DDA52';
$colour_line = '2EAC6D';
$colour_area_max = 'FFEE99';
$graph_max = 10000;
$unit_text = 'Calls';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'freeswitch', 'stats', $app['app_id']]);

require 'includes/html/graphs/generic_simplex.inc.php';
