<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_congestion');

$ds = 'congestion';

$colour_area = 'cc3333';
$colour_line = 'ff3333';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Congestion';

require 'includes/html/graphs/generic_simplex.inc.php';
