<?php

/// Draw generic bits graph
/// args: rra_in, rra_out, rrd_filename, bg, legend, from, to, width, height, inverse, percentile

include("includes/graphs/common.inc.php");

$unit_text = str_pad(truncate($unit_text,18,''),18);
$line_text = str_pad(truncate($line_text,12,''),12);

$rrd_options .= " DEF:".$rra."=".$rrd_filename.":".$rra.":AVERAGE";
$rrd_options .= " DEF:".$rra."_max=".$rrd_filename.":".$rra.":MAX";

if($print_total) {
  $rrd_options .= " VDEF:".$rra."_total=rra,TOTAL";
}

if($percentile) {
  $rrd_options .= " VDEF:".$rra."_percentile=".$rra.",".$percentile.",PERCENT";
}

if($graph_max) {
  $rrd_options .= " AREA:".$rra."_max#".$colour_area_max.":";
}
$rrd_options .= " AREA:".$rra."#".$colour_area.":";
$rrd_options .= " COMMENT:'".$unit_text."Now       Ave      Max";
if($percentile) {
  $rrd_options .= "      ".$percentile."th %";
}
$rrd_options .= "\\n'";
$rrd_options .= " LINE1.25:".$rra."#".$colour_line.":'".$line_text."'";
$rrd_options .= " GPRINT:".$rra.":LAST:%6.2lf%s";
$rrd_options .= " GPRINT:".$rra.":AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:".$rra."_max:MAX:%6.2lf%s";
if($percentile) {
  $rrd_options .= " GPRINT:".$rra."_percentile:%6.2lf%s";
}
$rrd_options .= "\\\\n";
$rrd_options .= " COMMENT:\\\\n";
if($print_total) {
  $rrd_options .= " GPRINT:".$rra."_tot:Total\ %6.2lf%s\)\\\\l";
}
if($percentile) {
  $rrd_options .= " LINE1:".$rra."_percentile#aa0000";
}

?>
