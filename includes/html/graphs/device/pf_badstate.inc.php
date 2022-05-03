<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_badstate');

$ds = 'badstate';

$colour_area = 'cc0033';
$colour_line = 'ff0033';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'BadState';

require 'includes/html/graphs/generic_simplex.inc.php';
