<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_ipoption');

$ds = 'ipoption';

$colour_area = 'cc6600';
$colour_line = 'ff6600';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'IPOption';

require 'includes/html/graphs/generic_simplex.inc.php';
