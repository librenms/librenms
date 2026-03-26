<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'apache', $app->app_id]);

$ds = 'kbyte';

$colour_area = 'CDEB8B';
$colour_line = '006600';

$colour_area_max = 'FFEE99';

$graph_max = 1;
$multiplier = 8;

$unit_text = 'Kbps';

require 'includes/html/graphs/generic_simplex.inc.php';
