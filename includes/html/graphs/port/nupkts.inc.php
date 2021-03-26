<?php

$rrd_file = get_port_rrdfile_path($device['hostname'], $port['port_id']);

$rrd_list[2]['filename'] = $rrd_file;
$rrd_list[2]['descr'] = $int['ifDescr'];
$rrd_list[2]['ds_in'] = 'INBROADCASTPKTS';
$rrd_list[2]['ds_out'] = 'OUTBROADCASTPKTS';
$rrd_list[2]['descr'] = 'Broadcast';
$rrd_list[2]['colour_area_in'] = '085F63';
$rrd_list[2]['colour_area_out'] = '49BEB7';

$rrd_list[4]['filename'] = $rrd_file;
$rrd_list[4]['descr'] = $int['ifDescr'];
$rrd_list[4]['ds_in'] = 'INMULTICASTPKTS';
$rrd_list[4]['ds_out'] = 'OUTMULTICASTPKTS';
$rrd_list[4]['descr'] = 'Multicast';
$rrd_list[4]['colour_area_in'] = 'FACF5A';
$rrd_list[4]['colour_area_out'] = 'FF5959';

$units = '';
$units_descr = 'Packets';
$total_units = 'pps';
$colours_in = 'purples';
$multiplier = '1';
$colours_out = 'oranges';

$nototal = 1;

include 'includes/html/graphs/generic_multi_seperated.inc.php';
