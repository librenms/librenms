<?php

$rrd_filename = Rrd::name($device['hostname'], 'fortigate_cpu');

require 'includes/html/graphs/common.inc.php';

$ds = 'LOAD';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = '% CPU';

require 'includes/html/graphs/generic_simplex.inc.php';
