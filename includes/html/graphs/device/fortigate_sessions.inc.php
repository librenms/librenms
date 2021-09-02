<?php

$rrd_filename = Rrd::name($device['hostname'], 'fortigate_sessions');

require 'includes/html/graphs/common.inc.php';

$ds = 'sessions';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';
