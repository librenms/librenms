<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_memdropped');

$ds = 'memdropped';

$colour_area = 'cc0000';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'MemDropped';

require 'includes/html/graphs/generic_simplex.inc.php';
