<?php

$rrd_filename = Rrd::name($device['hostname'], ["vm.diskio", ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$ds_in = 'read_bytes';
$ds_out = 'write_bytes';
$unit_text = 'Bits/sec';
$multiplier = 8;
$colour_area_in = 'FF0000';
$colour_line_in = '990000';
$colour_area_out = '00FF00';
$colour_line_out = '009900';


require 'includes/html/graphs/generic_duplex.inc.php';
