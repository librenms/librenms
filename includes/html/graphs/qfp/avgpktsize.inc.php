<?php

$colour_area_in = 'AA66AA';
$colour_line_in = '330033';

$colour_area_out = 'FFDD88';
$colour_line_out = 'FF6600';

$colour_area_in_max = 'cc88cc';
$colour_area_out_max = 'FFefaa';

$graph_max = 1;

require 'includes/html/graphs/common.inc.php';

$stacked = generate_stacked_graphs();

$length = '10';

if (!isset($out_text)) {
    $out_text = 'Out';
}

if (!isset($in_text)) {
    $in_text = 'In';
}

$unit_text = str_pad(truncate($unit_text, $length), $length);
$in_text = str_pad(truncate($in_text, $length), $length);
$out_text = str_pad(truncate($out_text, $length), $length);

$rrd_options .= ' DEF:in_packets=' . $rrd_filename . ':InTotalPps:AVERAGE';
$rrd_options .= ' DEF:out_packets=' . $rrd_filename . ':OutTotalPps:AVERAGE';
$rrd_options .= ' DEF:in_bits=' . $rrd_filename . ':InTotalBps:AVERAGE';
$rrd_options .= ' DEF:out_bits=' . $rrd_filename . ':OutTotalBps:AVERAGE';

$rrd_options .= ' CDEF:in_throughput=in_bits,8,/';
$rrd_options .= ' CDEF:out_throughput=out_bits,8,/';

$rrd_options .= ' CDEF:in_avg=in_throughput,in_packets,/';
$rrd_options .= ' CDEF:out_avg_tmp=out_throughput,out_packets,/';
$rrd_options .= ' CDEF:out_avg=out_avg_tmp,-1,*';


$rrd_options .= ' AREA:in_avg#' . $colour_area_in . $stacked['transparency'] . ':';
$rrd_options .= " COMMENT:'Average packet size\\n'";
$rrd_options .= ' LINE1.25:in_avg#' . $colour_line_in . ":'" . $in_text . "'";
$rrd_options .= ' GPRINT:in_avg:AVERAGE:%6.2lf%sB';
$rrd_options .= " COMMENT:\\n";

$rrd_options .= ' AREA:out_avg#' . $colour_area_out . $stacked['transparency'] . ':';
$rrd_options .= ' LINE1.25:out_avg#' . $colour_line_out . ":'" . $out_text . "'";
$rrd_options .= ' GPRINT:out_avg_tmp:AVERAGE:%6.2lf%sB';
$rrd_options .= " COMMENT:\\n";

$rrd_options .= ' HRULE:0#999999';

unset($stacked);
