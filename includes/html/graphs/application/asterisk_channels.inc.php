<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds = 'channels';
$colour_area = '9DDA52';
$colour_line = '2EAC6D';
$colour_area_max = 'FFEE99';
$graph_max = 20000;
$unit_text = 'Channels';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'asterisk', 'stats', $app['app_id']]);

require 'includes/html/graphs/generic_simplex.inc.php';
