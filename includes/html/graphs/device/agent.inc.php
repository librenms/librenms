<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$agent_rrd = rrd_name($device['hostname'], 'agent');

if (rrdtool_check_rrd_exists($agent_rrd)) {
    $rrd_filename = $agent_rrd;
}

$ds = 'time';

$colour_area = 'EEEEEE';
$colour_line = '36393D';

$colour_area_max = 'FFEE99';

$graph_max         = 1;
$multiplier        = 1000;
$multiplier_action = '/';

$unit_text = 'Seconds';

require 'includes/html/graphs/generic_simplex.inc.php';
