<?php

$rrd_filename = Rrd::name($device['hostname'], 'asyncos_conns');

require 'includes/html/graphs/common.inc.php';

$ds = 'connections';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Connections';

require 'includes/html/graphs/generic_simplex.inc.php';
