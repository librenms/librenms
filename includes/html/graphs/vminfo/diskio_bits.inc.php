<?php

$rrd_filename = Rrd::metricName($vminfo->metricIdentity('vm.diskio'));

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$ds_in = 'read_bytes';
$ds_out = 'write_bytes';
$unit_text = 'Bits/sec';
$multiplier = 8;
$colour_area_in = 'CAE853';
$colour_line_in = '75BA30';
$colour_area_out = 'A9A9F2';
$colour_line_out = '6A6AB7';

require 'includes/html/graphs/generic_duplex.inc.php';
