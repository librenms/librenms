<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_statelimit');

$ds = 'statelimit';

$colour_area = 'cc3366';
$colour_line = 'ff3366';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'StateLimit';

require 'includes/html/graphs/generic_simplex.inc.php';
