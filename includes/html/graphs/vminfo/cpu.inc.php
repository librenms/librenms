<?php

$rrd_filename = Rrd::name($device['hostname'], ['vm.cpu', ...$vminfo->tags()]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['ds'] = 'usage';
$rrd_list[0]['descr'] = 'CPU Usage';
$rrd_list[0]['area'] = 1;

$unit_text = 'CPU Usage %%';
$units = '%%';
$total_units = '%%';
$colours = 'mixed';
$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/generic_multi_line.inc.php';
