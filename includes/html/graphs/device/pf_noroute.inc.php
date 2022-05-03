<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_noroute');

$ds = 'noroute';

$colour_area = 'cc6633';
$colour_line = 'ff6633';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'NoRoute';

require 'includes/html/graphs/generic_simplex.inc.php';
