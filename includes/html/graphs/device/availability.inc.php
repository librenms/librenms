<?php

$scale_min = '0';
$scale_max = '100';
$float_precision = '3';

$rrd_filename = Rrd::name($device['hostname'], ['availability', $vars['duration']]);

$ds = 'availability';

$colour_line = '000000';
$colour_area = '8B8BEB44';

$colour_area_max = 'cc9999';

$line_text = \LibreNMS\Util\Time::formatInterval($vars['duration'], parts: 1);

$graph_title .= '::' . $line_text;

$graph_max = 1;

$unit_text = 'Availability(%)';

require 'includes/html/graphs/generic_simplex.inc.php';
