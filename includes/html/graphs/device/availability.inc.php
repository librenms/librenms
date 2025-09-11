<?php

$ds = 'availability';
$unit_text = 'Availability(%)';
$float_precision = '3';
$scale_min = '0';
$scale_max = '100';

$duration = $vars['duration'] ?? 86400;
if ($duration > 86400) {
    $rrd_filename = Rrd::name($device['hostname'], ['availability', $duration]);

    $colour_line = '000000';
    $colour_area = '8B8BEB44';

    $colour_area_max = 'cc9999';

    $line_text = \LibreNMS\Util\Time::formatInterval($duration);

    $graph_title .= '::' . $line_text;

    $graph_max = 1;

    require 'includes/html/graphs/generic_simplex.inc.php';
} else {
    $filename = Rrd::name($device['hostname'], ['availability', $duration]);
    $descr = '';

    require 'includes/html/graphs/generic_stats.inc.php';
}
