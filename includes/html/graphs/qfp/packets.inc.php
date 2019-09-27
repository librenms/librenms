<?php

$i = 1;
$rrd_list[$i]['filename']        = $rrd_filename;
$rrd_list[$i]['descr']           = $components['name'];
$rrd_list[$i]['ds_in']           = 'InPriorityPps';
$rrd_list[$i]['ds_out']          = 'OutPriorityPps';
$rrd_list[$i]['descr']           = 'Priority';
$rrd_list[$i]['colour_area_in']  = 'FACF5A';
$rrd_list[$i]['colour_area_out'] = 'FF5959';

$i =2;
$rrd_list[$i]['filename']        = $rrd_filename;
$rrd_list[$i]['descr']           = $components['name'];
$rrd_list[$i]['ds_in']           = 'InNonPriorityPps';
$rrd_list[$i]['ds_out']          = 'OutNonPriorityPps';
$rrd_list[$i]['descr']           = 'NonPriority';
$rrd_list[$i]['colour_area_in']  = '085F63';
$rrd_list[$i]['colour_area_out'] = '49BEB7';



$units       = 'pps';
$units_descr = 'Packets/s';
$colours_in  = 'purples';
$multiplier  = '1';
$colours_out = 'oranges';

$args['nototal'] = 1;

include 'includes/html/graphs/generic_multi_seperated.inc.php';
