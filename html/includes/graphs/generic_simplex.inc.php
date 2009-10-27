<?php

/// Draw generic bits graph
/// args: rra_in, rra_out, rrd_filename, bg, legend, from, to, width, height, inverse, $percentile

include("common.inc.php");

if(!$unit_text) {$unit_text = "\ \ \ \ \ \ \ ";}

$rrd_options .= " DEF:".$in."=".$rrd_filename.":".$rra_in.":AVERAGE";
$rrd_options .= " DEF:".$in."_max=".$rrd_filename.":".$rra_out.":MAX";

if($print_total) {
  $rrd_options .= " VDEF:totin=in,TOTAL";
}

if($percentile) {
  $rrd_options .= " VDEF:percentile_in=in,".$percentile.",PERCENT";
}

if($graph_max) {
  $rrd_options .= " AREA:in_max#".$colour_area_in_max.":";
}
$rrd_options .= " AREA:in#".$colour_area_in.":";
$rrd_options .= " COMMENT:".$unit_text."Now\ \ \ \ \ \ \ Ave\ \ \ \ \ \ Max";
if($percentile) {
  $rrd_options .= "\ \ \ \ \ \ ".$percentile."th\ %\\\\n";
}
$rrd_options .= "\\\\n";
$rrd_options .= " LINE1.25:in#".$colour_line_in.":In\ ";
$rrd_options .= " GPRINT:in:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:in:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:in_max:MAX:%6.2lf%s";
if($percentile) {
  $rrd_options .= " GPRINT:percentile_in:%6.2lf%s";
}
$rrd_options .= "\\\\n";
$rrd_options .= " COMMENT:\\\\n";
if($print_total) {
  $rrd_options .= " GPRINT:tot:Total\ %6.2lf%s\)\\\\l";
}
if($percentile) {
  $rrd_options .= " LINE1:percentile_in#aa0000";
}

?>
