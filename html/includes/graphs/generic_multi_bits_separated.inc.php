<?php

include("includes/graphs/common.inc.php");

$i = 0;

$rrd_options .= " COMMENT:'                     In\: Current     Maximum      '";
if (!$nototal) { $rrd_options .= " COMMENT:'Total      '"; }
$rrd_options .= " COMMENT:'Out\: Current     Maximum'";
if (!$nototal) { $rrd_options .= " COMMENT:'     Total'"; }
$rrd_options .= " COMMENT:'\\n'";
if(!isset($multiplier)) { $multiplier = "8"; }

foreach ($rrd_list as $rrd)
{
  if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) { $iter = 0; }

  $colour_in=$config['graph_colours'][$colours_in][$iter];
  $colour_out=$config['graph_colours'][$colours_out][$iter];

  $descr = str_replace(":", "\:", substr(str_pad($rrd['descr'], 18),0,18));

  $rrd_options .= " DEF:".$in.$i."=".$rrd['filename'].":".$rra_in.":AVERAGE ";
  $rrd_options .= " DEF:".$out.$i."=".$rrd['filename'].":".$rra_out.":AVERAGE ";
  $rrd_options .= " CDEF:inB".$i."=in".$i.",$multiplier,* ";
  $rrd_options .= " CDEF:outB".$i."=out".$i.",$multiplier,*";
  $rrd_options .= " CDEF:outB".$i."_neg=outB".$i.",-1,*";
  $rrd_options .= " CDEF:octets".$i."=inB".$i.",outB".$i.",+";

  if (!$args['nototal'])
  {
    $rrd_options .= " VDEF:totin".$i."=inB".$i.",TOTAL";
    $rrd_options .= " VDEF:totout".$i."=outB".$i.",TOTAL";
    $rrd_options .= " VDEF:tot".$i."=octets".$i.",TOTAL";
  }

  $rrd_options .= " HRULE:999999999999999#" . $colour_out . ":\\\s:";

  if ($i) { $stack="STACK"; }

  $rrd_options .= " AREA:inB".$i."#" . $colour_in . ":'" . $descr . "':$stack";
  $rrd_optionsb .= " AREA:outB".$i."_neg#" . $colour_out . "::$stack";
  $rrd_options .= " GPRINT:inB".$i.":LAST:%6.2lf%s$units";
  $rrd_options .= " GPRINT:inB".$i.":MAX:%6.2lf%s$units";

  if (!$nototal) { $rrd_options .= " GPRINT:totin".$i.":%6.2lf%s$total_units"; }

  $rrd_options .= " COMMENT:'    '";
  $rrd_options .= " GPRINT:outB".$i.":LAST:%6.2lf%s$units";
  $rrd_options .= " GPRINT:outB".$i.":MAX:%6.2lf%s$units";

  if (!$nototal) { $rrd_options .= " GPRINT:totout".$i.":%6.2lf%s$total_unit"; }

  $rrd_options .= " COMMENT:\\\\n";
  $i++; $iter++;
}




if ($custom_graph) { $rrd_options .= $custom_graph; }

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#999999";

?>
