<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_removals');

$ds = 'removals';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Removals';

require 'includes/html/graphs/generic_simplex.inc.php';
