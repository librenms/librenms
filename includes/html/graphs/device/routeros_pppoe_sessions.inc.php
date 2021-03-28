<?php

$rrd_filename = Rrd::name($device['hostname'], 'routeros_pppoe_sessions');

require 'includes/html/graphs/common.inc.php';

$ds = 'pppoe_sessions';

$colour_area = '99cc99';
$colour_line = '00cc00';

$colour_area_max = '99cc99';
$scale_min = '0';
$graph_max = 1;
$graph_min = 0;

$unit_text = 'PPPoE Sessions';

require 'includes/html/graphs/generic_simplex.inc.php';
