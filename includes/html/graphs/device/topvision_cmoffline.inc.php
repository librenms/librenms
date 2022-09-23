<?php

$rrd_filename = Rrd::name($device['hostname'], 'topvision_cmoffline');

$ds = 'cmoffline';

$colour_line = '990000';
$colour_area = 'e60000';
$colour_area_max = '9999cc';

$scale_min = '0';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'CM Offline';

require 'includes/html/graphs/generic_simplex.inc.php';
