<?php

$rrd_filename = Rrd::metricName($vminfo->metricIdentity('vm.net'));

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$colour_area_in = '90B040';
$colour_line_in = '608720';
$colour_area_out = '8080C0';
$colour_line_out = '606090';
$colour_area_in_max = 'D7FFC7';
$colour_area_out_max = 'E0E0FF';

$ds_in = 'in';
$ds_out = 'out';

$unit_text = 'Bits/sec';
$multiplier = 8;

require 'includes/html/graphs/generic_duplex.inc.php';
