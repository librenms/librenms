<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -b 1024';

$iter = '1';

$rrd_options .= " COMMENT:'                                               % Used\\n'";

$hostname = gethostbyid($router_utilization['device_id']);

$colour      = 'CC0000';
$colour_area = 'ffaaaa';

if ($router_utilization['feature']) {
    $label = $router_utilization['resource'] . ' - ' . $router_utilization['feature'];
} else {
    $label = $router_utilization['resource'];
}
$descr = rrdtool_escape($label, 42);

$percentage = round($router_utilization['current'] / $router_utilization['maximum'] * 100, 0);

$background = get_percentage_colours($percentage, 80);

$rrd_options .= " DEF:perc=$rrd_filename:used:AVERAGE";
$rrd_options .= ' AREA:perc#' . $background['right'] . ':';
$rrd_options .= ' LINE1.25:perc#'. $background['left'] .":'$descr'";
$rrd_options .= " GPRINT:perc:LAST:%5.2lf%%\\n";
