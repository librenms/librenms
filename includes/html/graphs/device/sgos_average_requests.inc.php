<?php

$rrd_filename = Rrd::name($device['hostname'], 'sgos_average_requests');

require 'includes/html/graphs/common.inc.php';

$ds = 'requests';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;
$graph_min = 0;

$unit_text = 'Requests';

require 'includes/html/graphs/generic_simplex.inc.php';
