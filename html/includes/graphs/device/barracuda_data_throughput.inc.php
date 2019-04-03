<?php

$rrd_filename = rrd_name($device['hostname'], 'barracuda_data_throughput');

require 'includes/graphs/common.inc.php';

$ds = 'data_throughput';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

#$graph_max = 1;
$graph_min = 0;

$unit_text = 'bps';

require 'includes/graphs/generic_simplex.inc.php';
