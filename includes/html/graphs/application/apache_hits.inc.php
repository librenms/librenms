<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'apache', $app->app_id]);

$ds = 'access';

$colour_area = 'B0C4DE';
$colour_line = '191970';

$colour_area_max = 'FFEE99';

$graph_max = 1;

$unit_text = 'Hits/sec';

require 'includes/html/graphs/generic_simplex.inc.php';
