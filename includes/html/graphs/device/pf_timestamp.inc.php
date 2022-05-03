<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_timestamp');

$ds = 'timestamp';

$colour_area = 'cc9900';
$colour_line = 'ff9900';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Timestamp';

require 'includes/html/graphs/generic_simplex.inc.php';
