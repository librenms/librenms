<?php

/// Draws aggregate bits graph from multiple RRDs
/// Variables : colour_[line|area]_[in|out], rrd_filenames

include("includes/graphs/common.inc.php");

if($format == "octets" || $format == "bytes")
{
  $units = "Bps";
  $format = "octets";
} else {
  $units = "bps";
  $format = "bits";
}

$i=0;

foreach ($rrd_filenames as $key => $rrd_filename)
{
  if ($rrd_inverted[$key]) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }

  $rrd_options .= " DEF:".$in."octets" . $i . "=".$rrd_filename.":".$ds_in.":AVERAGE";
  $rrd_options .= " DEF:".$out."octets" . $i . "=".$rrd_filename.":".$ds_out.":AVERAGE";
  $in_thing .= $seperator . "inoctets" . $i . ",UN,0," . "inoctets" . $i . ",IF";
  $out_thing .= $seperator . "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";
  $pluses .= $plus;
  $seperator = ",";
  $plus = ",+";

  if ($_GET['previous'])
  {
    $rrd_options .= " DEF:".$in."octets" . $i . "X=".$rrd_filename.":".$ds_in.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:".$out."octets" . $i . "X=".$rrd_filename.":".$ds_out.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$in."octets" . $i . "X:$period";
    $rrd_options .= " SHIFT:".$out."octets" . $i . "X:$period";
    $in_thingX .= $seperatorX . "inoctets" . $i . "X,UN,0," . "inoctets" . $i . "X,IF";
    $out_thingX .= $seperatorX . "outoctets" . $i . "X,UN,0," . "outoctets" . $i . "X,IF";
    $plusesX .= $plusX;
    $seperatorX = ",";
    $plusX = ",+";
  }
  $i++;
}

if ($i)
{
  if ($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $rrd_options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $rrd_options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
  $rrd_options .= " CDEF:inbits=inoctets,8,*";
  $rrd_options .= " CDEF:outbits=outoctets,8,*";
  $rrd_options .= " CDEF:doutbits=doutoctets,8,*";
  $rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
  $rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
  $rrd_options .= " VDEF:d95thout=doutbits,5,PERCENT";

  if ($_GET['previous'] == "yes")
  {
    $rrd_options .= " CDEF:".$in."octetsX=" . $in_thingX . $pluses;
    $rrd_options .= " CDEF:".$out."octetsX=" . $out_thingX . $pluses;
    $rrd_options .= " CDEF:doutoctetsX=outoctetsX,-1,*";
    $rrd_options .= " CDEF:inbitsX=inoctetsX,8,*";
    $rrd_options .= " CDEF:outbitsX=outoctetsX,8,*";
    $rrd_options .= " CDEF:doutbitsX=doutoctetsX,8,*";
    $rrd_options .= " VDEF:95thinX=inbitsX,95,PERCENT";
    $rrd_options .= " VDEF:95thoutX=outbitsX,95,PERCENT";
    $rrd_options .= " VDEF:d95thoutX=doutbitsX,5,PERCENT";
  }

  if ($legend == 'no' || $legend == '1')
  {
    $rrd_options .= " AREA:in".$format."#".$colour_area_in.":";
#    $rrd_options .= " LINE1.25:in".$format."#".$colour_line_in.":";
    $rrd_options .= " AREA:dout".$format."#".$colour_area_out.":";
#    $rrd_options .= " LINE1.25:dout".$format."#".$colour_line_out.":";
  } else {
    $rrd_options .= " AREA:in".$format."#".$colour_area_in.":";
    $rrd_options .= " COMMENT:'bps      Now       Ave      Max      95th %\\n'";
#    $rrd_options .= " LINE1.25:in".$format."#".$colour_line_in.":In\ ";
    $rrd_options .= " GPRINT:in".$format.":LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:in".$format.":AVERAGE:%6.2lf%s";
    $rrd_options .= " GPRINT:in".$format.":MAX:%6.2lf%s";
    $rrd_options .= " GPRINT:95thin:%6.2lf%s\\\\n";
    $rrd_options .= " AREA:dout".$format."#".$colour_area_out.":";
#    $rrd_options .= " LINE1.25:dout".$format."#".$colour_line_out.":Out";
    $rrd_options .= " GPRINT:out".$format.":LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:out".$format.":AVERAGE:%6.2lf%s";
    $rrd_options .= " GPRINT:out".$format.":MAX:%6.2lf%s";
    $rrd_options .= " GPRINT:95thout:%6.2lf%s\\\\n";
  }

  $rrd_options .= " LINE1:95thin#aa0000";
  $rrd_options .= " LINE1:d95thout#aa0000";

  if ($_GET['previous'] == "yes")
  {
    $rrd_options .= " AREA:in".$format."X#99999999:";
    $rrd_options .= " AREA:dout".$format."X#99999999:";
    $rrd_options .= " LINE1.25:in".$format."X#666666:";
    $rrd_options .= " LINE1.25:dout".$format."X#666666:";
  }

}

#$rrd_options .= " HRULE:0#999999";

?>
