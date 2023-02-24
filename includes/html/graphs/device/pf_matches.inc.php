<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_matches');

$ds = 'matches';

$colour_area = 'cc0000';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Matches';

require 'includes/html/graphs/generic_simplex.inc.php';
