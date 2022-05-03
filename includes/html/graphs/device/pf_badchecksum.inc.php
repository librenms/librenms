<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_badchecksum');

$ds = 'badchecksum';

$colour_area = 'cc0000';
$colour_line = 'ff0000';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'BadChecksum';

require 'includes/html/graphs/generic_simplex.inc.php';
