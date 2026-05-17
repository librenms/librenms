<?php

$units_descr = 'Operations/sec';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '1';
$colours_out = 'blues';

$ds_in = 'reads';
$ds_out = 'writes';

$nototal = 1;

require 'includes/html/graphs/mdadm/diskio_common.inc.php';

$ops_in_colours = ['FF3300', 'FF4D1A', 'FF6633', 'FF8059'];
$ops_out_colours = ['FF6633', 'FF7F50', 'FF9966', 'FFB380'];

foreach ($rrd_list as $index => $rrd) {
    $rrd_list[$index]['ds_in'] = $ds_in;
    $rrd_list[$index]['ds_out'] = $ds_out;
    $colour_index = $index % count($ops_in_colours);
    $rrd_list[$index]['colour_area_in'] = $ops_in_colours[$colour_index];
    $rrd_list[$index]['colour_area_out'] = $ops_out_colours[$colour_index];
}

require 'includes/html/graphs/generic_multi_seperated.inc.php';
