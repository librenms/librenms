<?php

$scale_min = '0';

$rrd_filename = Rrd::name($device['hostname'], 'hr_users');

$ds = 'users';

$colour_line = '008C00';
$colour_area = 'CDEB8B';

$colour_area_max = 'cc9999';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Users';

require 'includes/html/graphs/generic_simplex.inc.php';
