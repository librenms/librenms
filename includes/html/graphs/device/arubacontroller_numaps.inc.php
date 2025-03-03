<?php

$rrd_filename = Rrd::name($device['hostname'], 'aruba-controller');

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['descr'] = 'Number of APs';
$rrd_list[0]['ds'] = 'NUMAPS';

$unit_text = 'APs';
$units = '';
$total_units = '';
$colours = 'mixed';

$scale_min = '0';
$nototal = 1;

if ($rrd_list) {
    include 'includes/html/graphs/generic_multi_line.inc.php';
}
