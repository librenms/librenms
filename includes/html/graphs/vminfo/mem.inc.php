<?php

$rrd_filename = Rrd::name($device['hostname'], ['vm.memory', ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['ds'] = 'used';
$rrd_list[0]['descr'] = 'Used Memory';
$rrd_list[0]['area'] = 1;

$rrd_list[1]['filename'] = $rrd_filename;
$rrd_list[1]['ds'] = 'total';
$rrd_list[1]['descr'] = 'Total Memory';

$unit_text = 'Memory';
$colours = 'mixed';
$multiplier = 1024 * 1024; // MB to bytes

require 'includes/html/graphs/generic_multi_line.inc.php';
