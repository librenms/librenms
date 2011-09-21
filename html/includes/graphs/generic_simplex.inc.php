<?php

/// Draw generic bits graph
/// args: ds_in, ds_out, rrd_filename, bg, legend, from, to, width, height, inverse, percentile

include("includes/graphs/common.inc.php");

$unit_text = str_pad(truncate($unit_text,18,''),18);
$line_text = str_pad(truncate($line_text,12,''),12);

$rrd_options .= " DEF:".$ds."=".$rrd_filename.":".$ds.":AVERAGE";
$rrd_options .= " DEF:".$ds."_max=".$rrd_filename.":".$ds.":MAX";

if ($print_total)
{
  $rrd_options .= " VDEF:".$ds."_total=ds,TOTAL";
}

if ($percentile)
{
  $rrd_options .= " VDEF:".$ds."_percentile=".$ds.",".$percentile.",PERCENT";
}

if ($graph_max)
{
  $rrd_options .= " AREA:".$ds."_max#".$colour_area_max.":";
}
$rrd_options .= " AREA:".$ds."#".$colour_area.":";
$rrd_options .= " COMMENT:'".$unit_text."Now       Ave      Max";

if ($percentile)
{
  $rrd_options .= "      ".$percentile."th %";
}

$rrd_options .= "\\n'";
$rrd_options .= " LINE1.25:".$ds."#".$colour_line.":'".$line_text."'";
$rrd_options .= " GPRINT:".$ds.":LAST:%6.2lf%s";
$rrd_options .= " GPRINT:".$ds.":AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:".$ds."_max:MAX:%6.2lf%s";

if ($percentile)
{
  $rrd_options .= " GPRINT:".$ds."_percentile:%6.2lf%s";
}

$rrd_options .= "\\\\n";
$rrd_options .= " COMMENT:\\\\n";

if ($print_total)
{
  $rrd_options .= " GPRINT:".$ds."_tot:Total\ %6.2lf%s\)\\\\l";
}

if ($percentile)
{
  $rrd_options .= " LINE1:".$ds."_percentile#aa0000";
}

?>