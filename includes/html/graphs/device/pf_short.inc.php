<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_short');

$ds = 'short';

$colour_area = 'ffff00';
$colour_line = 'ffff66';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Short';

require 'includes/html/graphs/generic_simplex.inc.php';
