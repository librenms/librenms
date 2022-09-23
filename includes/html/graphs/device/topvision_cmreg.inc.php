<?php

$rrd_filename = Rrd::name($device['hostname'], 'topvision_cmreg');

$ds = 'cmreg';

$colour_area = '00b33c';
$colour_line = '006622';
$colour_area_max = '9999cc';

$scale_min = '0';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'CM Registered';

require 'includes/html/graphs/generic_simplex.inc.php';
