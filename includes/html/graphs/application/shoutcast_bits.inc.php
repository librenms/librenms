<?php

$units = 'b';
$total_units = 'B';
$colours_in = 'greens';
// $multiplier      = "0";
$colours_out = 'blues';

$nototal = 1;

$ds_in = 'traf_in';
$ds_out = 'traf_out';

$graph_title .= '::bits';

$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = 'CDEB8B';
$colour_area_out = 'C3D9FF';

$hostname = (isset($_GET['hostname']) ? $_GET['hostname'] : 'unknown');
$rrd_filename = Rrd::name($device['hostname'], ['app', 'shoutcast', $app['app_id'], $hostname]);

require 'includes/html/graphs/generic_data.inc.php';
