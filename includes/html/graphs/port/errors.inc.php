<?php

$rrd_list[1]['filename'] = $rrd_filename;
$rrd_list[1]['descr'] = $int['ifDescr'];
$rrd_list[1]['ds_in'] = 'INERRORS';
$rrd_list[1]['ds_out'] = 'OUTERRORS';
$rrd_list[1]['descr'] = 'Errors';
$rrd_list[1]['colour_area_in'] = 'FF3300';
$rrd_list[1]['colour_area_out'] = 'FF6633';

$rrd_list[4]['filename'] = $rrd_filename;
$rrd_list[4]['descr'] = $int['ifDescr'];
$rrd_list[4]['ds_in'] = 'INDISCARDS';
$rrd_list[4]['ds_out'] = 'OUTDISCARDS';
$rrd_list[4]['descr'] = 'Discards';
$rrd_list[4]['colour_area_in'] = '805080';
$rrd_list[4]['colour_area_out'] = 'c0a060';

$units = '';
$units_descr = 'Packets/sec';
$total_units = 'pps';
$colours_in = 'greens';
$multiplier = '1';
$colours_out = 'blues';

$nototal = 1;

require 'includes/html/graphs/generic_multi_seperated.inc.php';
