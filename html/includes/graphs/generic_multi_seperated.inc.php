<?php

include("includes/graphs/common.inc.php");

$units_descr = substr(str_pad($units_descr, 18),0,18);

if($format == "octets" || $format == "bytes")
{
  $units = "Bps";
  $format = "octets";
} else {
  $units = "bps";
  $format = "bits";
}

$i = 0;
$rrd_options .= " COMMENT:'$units_descr Current  Average  Maximum\\n'";
if (!$nototal) { $rrd_options .= " COMMENT:' Tot'"; }
$rrd_options .= " COMMENT:'\\n'";

foreach ($rrd_list as $rrd)
{
  if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) { $iter = 0; }

  $colour_in=$config['graph_colours'][$colours_in][$iter];
  $colour_out=$config['graph_colours'][$colours_out][$iter];

  if ($rrd['colour_area_in']) { $colour_in = $rrd['colour_area_in']; }
  if ($rrd['colour_area_out']) { $colour_out = $rrd['colour_area_out']; }

  $rrd_options .= " DEF:in".$i."=".$rrd['filename'].":".$rrd['ds_in'].":AVERAGE ";
  $rrd_options .= " DEF:out".$i."=".$rrd['filename'].":".$rrd['ds_out'].":AVERAGE ";
  $rrd_options .= " CDEF:inB".$i."=in".$i.",$multiplier,* ";
  $rrd_options .= " CDEF:outB".$i."=out".$i.",$multiplier,*";
  $rrd_options .= " CDEF:outB".$i."_neg=outB".$i.",-1,*";
  $rrd_options .= " CDEF:octets".$i."=inB".$i.",outB".$i.",+";

  if($_GET['previous'])
  {
    $rrd_options .= " DEF:".$in."octets" . $i . "X=".$rrd['filename'].":".$ds_in.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:".$out."octets" . $i . "X=".$rrd['filename'].":".$ds_out.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$in."octets" . $i . "X:$period";
    $rrd_options .= " SHIFT:".$out."octets" . $i . "X:$period";
    $in_thingX .= $seperatorX . "inoctets" . $i . "X,UN,0," . "inoctets" . $i . "X,IF";
    $out_thingX .= $seperatorX . "outoctets" . $i . "X,UN,0," . "outoctets" . $i . "X,IF";
    $plusesX .= $plusX;
    $seperatorX = ",";
    $plusX = ",+";
  }

  if (!$args['nototal'])
  {
    $rrd_options .= " VDEF:totin".$i."=inB".$i.",TOTAL";
    $rrd_options .= " VDEF:totout".$i."=outB".$i.",TOTAL";
    $rrd_options .= " VDEF:tot".$i."=octets".$i.",TOTAL";
  }

  if ($i) { $stack="STACK"; }

  $rrd_options .= " AREA:inB".$i."#" . $colour_in . ":'" . substr(str_pad($rrd['descr'], 10),0,10) . "In ':$stack";
  $rrd_options .= " GPRINT:inB".$i.":LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:inB".$i.":AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:inB".$i.":MAX:%6.2lf%s";

  if (!$nototal) { $rrd_options .= " GPRINT:totin".$i.":%6.2lf%s$total_units"; }

  $rrd_options .= " COMMENT:'\\n'";
  $rrd_optionsb .= " AREA:outB".$i."_neg#" . $colour_out . "::$stack";
  $rrd_options .= "  HRULE:999999999999999#" . $colour_out . ":'" . substr(str_pad('', 10),0,10) . "Out':";
  $rrd_options .= " GPRINT:outB".$i.":LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:outB".$i.":AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:outB".$i.":MAX:%6.2lf%s";

  if (!$nototal) { $rrd_options .= " GPRINT:totout".$i.":%6.2lf%s$total_unit"; }

  $rrd_options .= " COMMENT:'\\n'";
  $i++; $iter++;
}

  if($_GET['previous'] == "yes")
  {
    $rrd_options .= " CDEF:".$in."octetsX=" . $in_thingX . $plusesX;
    $rrd_options .= " CDEF:".$out."octetsX=" . $out_thingX . $plusesX;
    $rrd_options .= " CDEF:doutoctetsX=outoctetsX,-1,*";
    $rrd_options .= " CDEF:inbitsX=inoctetsX,8,*";
    $rrd_options .= " CDEF:outbitsX=outoctetsX,8,*";
    $rrd_options .= " CDEF:doutbitsX=doutoctetsX,8,*";
    $rrd_options .= " VDEF:95thinX=inbitsX,95,PERCENT";
    $rrd_options .= " VDEF:95thoutX=outbitsX,95,PERCENT";
    $rrd_options .= " VDEF:d95thoutX=doutbitsX,5,PERCENT";
  }

  if($_GET['previous'] == "yes")
  {
    $rrd_options .= " AREA:in".$format."X#99999999:";
    $rrd_options .= " AREA:dout".$format."X#99999999:";
    $rrd_options .= " LINE1.25:in".$format."X#666666:";
    $rrd_options .= " LINE1.25:dout".$format."X#666666:";
  }


$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#999999";

?>
