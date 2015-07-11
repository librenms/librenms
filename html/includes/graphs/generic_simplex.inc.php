<?php

if($config['old_graphs'])
{
  include("includes/graphs/old_generic_simplex.inc.php");
} else {

// Draw generic bits graph
// args: ds_in, ds_out, rrd_filename, bg, legend, from, to, width, height, inverse, percentile

include("includes/graphs/common.inc.php");

$unit_text = str_pad(truncate($unit_text,18,''),18);
$line_text = str_pad(truncate($line_text,12,''),12);

if ($multiplier)
{
  $rrd_options .= " DEF:".$ds."_o=".$rrd_filename.":".$ds.":AVERAGE";
  $rrd_options .= " DEF:".$ds."_max_o=".$rrd_filename.":".$ds.":MAX";
  $rrd_options .= " DEF:".$ds."_min_o=".$rrd_filename.":".$ds.":MIN";

  if (!isset($multiplier_action)) {
      $multiplier_action = "*";
  }
  $rrd_options .= " CDEF:".$ds."=".$ds."_o,$multiplier,$multiplier_action";
  $rrd_options .= " CDEF:".$ds."_max=".$ds."_max_o,$multiplier,$multiplier_action";
  $rrd_options .= " CDEF:".$ds."_min=".$ds."_min_o,$multiplier,$multiplier_action";
} else {
  $rrd_options .= " DEF:".$ds."=".$rrd_filename.":".$ds.":AVERAGE";
  $rrd_options .= " DEF:".$ds."_max=".$rrd_filename.":".$ds.":MAX";
  $rrd_options .= " DEF:".$ds."_min=".$rrd_filename.":".$ds.":MIN";
}
if ($print_total)
{
  $rrd_options .= " VDEF:".$ds."_total=ds,TOTAL";
}

if ($percentile)
{
  $rrd_options .= " VDEF:".$ds."_percentile=".$ds.",".$percentile.",PERCENT";
}

if($_GET['previous'] == "yes")
{
  if ($multiplier)
  {
    $rrd_options .= " DEF:".$ds."_oX=".$rrd_filename.":".$ds.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:".$ds."_max_oX=".$rrd_filename.":".$ds.":MAX:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$ds."_oX:$period";
    $rrd_options .= " SHIFT:".$ds."_max_oX:$period";
    if (!isset($multiplier_action)) {
        $multiplier_action = "*";
    }
    $rrd_options .= " CDEF:".$ds."X=".$ds."_oX,$multiplier,$multiplier_action";
    $rrd_options .= " CDEF:".$ds."_maxX=".$ds."_max_oX,$multiplier,$multiplier_action";
  } else {
    $rrd_options .= " DEF:".$ds."X=".$rrd_filename.":".$ds.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:".$ds."_maxX=".$rrd_filename.":".$ds.":MAX:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$ds."X:$period";
    $rrd_options .= " SHIFT:".$ds."_maxX:$period";
  }
  if ($print_total)
  {
    $rrd_options .= " VDEF:".$ds."_totalX=ds,TOTAL";
  }
  if ($percentile)
  {
    $rrd_options .= " VDEF:".$ds."_percentileX=".$ds.",".$percentile.",PERCENT";
  }
#  if ($graph_max)
#  {
#    $rrd_options .= " AREA:".$ds."_max#".$colour_area_max.":";
#  }
}

if($colour_minmax)
{
  $rrd_options .= " AREA:".$ds."_max#c5c5c5";
  $rrd_options .= " AREA:".$ds."_min#ffffffff";
} else {
  $rrd_options .= " AREA:".$ds."#".$colour_area.":";
  if ($graph_max)
  {
    $rrd_options .= " AREA:".$ds."_max#".$colour_area_max.":";
  }
}

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

if($_GET['previous'] == "yes")
{
  $rrd_options .= " LINE1.25:".$ds."X#666666:'Prev \\\\n'";
  $rrd_options .= " AREA:".$ds."X#99999966:";

}

}

?>
