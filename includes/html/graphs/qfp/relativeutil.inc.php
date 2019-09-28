<?php

$colour_line   = 'cc0000';
$colour_area   = 'FFBBBB';
$colour_minmax = 'c5c5c5';

$graph_max = 1;
$line_text = $components['name'];
include 'includes/html/graphs/common.inc.php';

$line_text = str_pad(substr($line_text, 0, 12), 12);


$rrd_options .= ' DEF:in_pkts='.$rrd_filename.':InTotalPps:AVERAGE';
$rrd_options .= ' DEF:out_pkts='.$rrd_filename.':OutTotalPps:AVERAGE';
$rrd_options .= ' DEF:load='.$rrd_filename.':ProcessingLoad:AVERAGE';
$rrd_options .= ' CDEF:total_kpps=in_pkts,out_pkts,+,1000,/';
$rrd_options .= ' CDEF:relative=load,total_kpps,/';

$rrd_options .= ' AREA:relative#'.$colour_area.':';
$rrd_options .= " COMMENT:'Load % per 1kpps'\\n";
$rrd_options .= ' LINE1.25:relative#'.$colour_line.":'".$line_text."'";
$rrd_options .= " COMMENT:\\n";
