<?php

$scale_min = 1000;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'opensips', $app->app_id]);

$ds = 'openfiles';

$colour_area = 'F0E68C';
$colour_line = 'FF4500';

$colour_area_max = 'FFEE99';

$graph_max = 1000000;

$unit_text = 'Open files';

require 'includes/html/graphs/generic_simplex.inc.php';
