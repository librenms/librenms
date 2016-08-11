<?php

$rrd_filename = rrd_name($device['hostname'], 'pulse_sessions');

require 'includes/graphs/common.inc.php';

$ds = 'sessions';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Sessions';

require 'includes/graphs/generic_simplex.inc.php';
