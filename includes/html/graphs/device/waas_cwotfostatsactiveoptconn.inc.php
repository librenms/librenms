<?php

$rrd_filename = Rrd::name($device['hostname'], 'waas_cwotfostatsactiveoptconn');

require 'includes/html/graphs/common.inc.php';

$ds = 'connections';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Optimized TCP Connections';

require 'includes/html/graphs/generic_simplex.inc.php';
