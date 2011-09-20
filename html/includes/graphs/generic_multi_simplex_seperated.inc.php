<?php

include("includes/graphs/common.inc.php");


$descrlen = "18";
$unitlen  = "10";
if($nototal) { $descrlen += "2"; $unitlen += "2";}
$unit_text = str_pad(truncate($unit_text,$unitlen),$unitlen);

$rrd_options .= " COMMENT:'$unit_text              Cur      Max'";
if (!$nototal) { $rrd_options .= " COMMENT:'Total      '"; }
$rrd_options .= " COMMENT:'\\n'";

$colour_iter=0;
foreach ($rrd_list as $i => $rrd)
{
  if (!$config['graph_colours'][$colours][$colour_iter]) { $colour_iter = 0; }
  $colour = $config['graph_colours'][$colours][$colour_iter];
  $colour_iter++;

  $rrd_options .= " DEF:".$rrd['rra'].$i."=".$rrd['filename'].":".$rrd['rra'].":AVERAGE ";
  $rrd_options .= " DEF:".$rrd['rra'].$i."max=".$rrd['filename'].":".$rrd['rra'].":MAX ";

  ## Suppress totalling?
  if (!$nototal)
  {
    $rrd_options .= " VDEF:tot".$rrd['rra'].$i."=".$rrd['rra'].$i.",TOTAL";
  }

  ## This this not the first entry?
  if ($i) { $stack="STACK"; }

  # if we've been passed a multiplier we must make a CDEF based on it!
  $g_defname = $rrd['rra'];
  if (is_numeric($multiplier))
  {
    $g_defname = $rrd['rra'] . "_cdef";
    $rrd_options .= " CDEF:" . $g_defname . $i . "=" . $rrd['rra'] . $i . "," . $multiplier . ",*";
    $rrd_options .= " CDEF:" . $g_defname . $i . "max=" . $rrd['rra'] . $i . "max," . $multiplier . ",*";

  ## If we've been passed a divider (divisor!) we make a CDEF for it.
  } elseif (is_numeric($divider))
  {
    $g_defname = $rrd['rra'] . "_cdef";
    $rrd_options .= " CDEF:" . $g_defname . $i . "=" . $rrd['rra'] . $i . "," . $divider . ",/";
    $rrd_options .= " CDEF:" . $g_defname . $i . "max=" . $rrd['rra'] . $i . "max," . $divider . ",/";
  }

  ## Are our text values related to te multiplier/divisor or not?
  if(isset($text_orig) && $text_orig)
  {
    $t_defname = $rrd['rra'];
  } else {
    $t_defname = $g_defname;
  }


  $rrd_options .= " AREA:".$g_defname.$i."#".$colour.":'".substr(str_pad($rrd['descr'], $descrlen),0,$descrlen)."':$stack";
  $rrd_options .= " GPRINT:".$t_defname.$i.":LAST:%6.2lf%s".str_replace("%", "%%", $units)."";
  $rrd_options .= " GPRINT:".$t_defname.$i."max:MAX:%6.2lf%s".str_replace("%", "%%", $units)."";

  if (!$nototal) { $rrd_options .= " GPRINT:tot".$rrd['rra'].$i.":%6.2lf%s".str_replace("%", "%%", $total_units).""; }

  $rrd_options .= " COMMENT:'\\n'";
}

?>
