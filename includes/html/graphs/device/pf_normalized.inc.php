<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_normalized');

$ds = 'normalized';

$colour_area = 'ff0066';
$colour_line = 'ff3399';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Normalized';

require 'includes/html/graphs/generic_simplex.inc.php';
