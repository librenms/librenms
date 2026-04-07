<?php

$rrd_filename = Rrd::name($device['hostname'], ['vm.disk', ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['ds'] = 'used';
$rrd_list[0]['descr'] = 'Used Disk';
$rrd_list[0]['area'] = 1;

$rrd_list[1]['filename'] = $rrd_filename;
$rrd_list[1]['ds'] = 'total';
$rrd_list[1]['descr'] = 'Total Disk';

$unit_text = 'Disk';
$colours = 'mixed';
$multiplier = 1024 * 1024; // MB to bytes

require 'includes/html/graphs/generic_multi_line.inc.php';
