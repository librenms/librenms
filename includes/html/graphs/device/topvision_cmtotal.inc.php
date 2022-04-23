<?php

$rrd_filename = Rrd::name($device['hostname'], 'topvision_cmtotal');

$ds = 'cmtotal';

$colour_area = '9999cc';
$colour_line = '0000cc';
$colour_area_max = '9999cc';

$scale_min = '0';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'CM Total';

require 'includes/html/graphs/generic_simplex.inc.php';
