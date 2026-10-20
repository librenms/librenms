<?php

$rrd_filename = Rrd::name($device['hostname'], ['vm.diskio', ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$colour_area_in = 'FF0000';
$colour_line_in = '990000';
$colour_area_out = '00FF00';
$colour_line_out = '009900';

$ds_in = 'read_ops';
$ds_out = 'write_ops';
$unit_text = 'Ops/sec';

require 'includes/html/graphs/generic_duplex.inc.php';
