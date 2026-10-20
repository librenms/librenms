<?php

$rrd_filename = Rrd::name($device['hostname'], ['vm.net', ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$colour_area_in = 'FF0000';
$colour_line_in = '990000';
$colour_area_out = '00FF00';
$colour_line_out = '009900';

$ds_in = 'in';
$ds_out = 'out';

$unit_text = 'Bits/sec';
$multiplier = 8;

require 'includes/html/graphs/generic_duplex.inc.php';
