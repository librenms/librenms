<?php

$rrd_filename = Rrd::name($device['hostname'], 'pf_badinsert');

$ds = 'badinsert';

$colour_area = 'cc3300';
$colour_line = 'ff3300';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'BadInsert';

require 'includes/html/graphs/generic_simplex.inc.php';
