<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_synproxy');

$ds = 'synproxy';

$colour_area = 'cc0066';
$colour_line = 'ff0066';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'SynProxy';

require 'includes/html/graphs/generic_simplex.inc.php';
