<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_srclimit');

$ds = 'srclimit';

$colour_area = 'cc6666';
$colour_line = 'ff6666';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'SrcLimit';

require 'includes/html/graphs/generic_simplex.inc.php';
