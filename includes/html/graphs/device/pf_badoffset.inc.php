<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_badoffset');

$ds = 'badoffset';

$colour_area = 'cc00ff';
$colour_line = 'cc33ff';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'BadOffset';

require 'includes/html/graphs/generic_simplex.inc.php';
