<?php

$rrd_filename = Rrd::name($device['hostname'], 'routeros_leases');

require 'includes/html/graphs/common.inc.php';

$ds = 'leases';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';
$scale_min = '0';
$graph_max = 1;
$graph_min = 0;

$unit_text = 'Leases';

require 'includes/html/graphs/generic_simplex.inc.php';
