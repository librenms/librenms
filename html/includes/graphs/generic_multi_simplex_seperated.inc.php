<?php

  include("common.inc.php");

  $unit_text = str_pad(truncate($unit_text,10),10);
  $rrd_options .= " COMMENT:'$unit_text              Cur      Max'";
  if(!$nototal) {$rrd_options .= " COMMENT:'Total      '";}
  $rrd_options .= " COMMENT:'\\n'";

  $i = 0;
  foreach($rrd_list as $rrd) {
      if(!$config['graph_colours'][$colours][$iter]) { $iter = 0; }
      $colour=$config['graph_colours'][$colours][$iter];
      $rrd_options .= " DEF:".$rrd['rra'].$i."=".$rrd['filename'].":".$rrd['rra'].":AVERAGE ";
      $rrd_options .= " DEF:".$rrd['rra'].$i."max=".$rrd['filename'].":".$rrd['rra'].":MAX ";

      if(!$args['nototal']) {
        $rrd_options .= " VDEF:tot".$rrd['rra'].$i."=".$rrd['rra'].$i.",TOTAL";
      }
      if($i) {$stack="STACK";}
      $rrd_options .= " AREA:".$rrd['rra'].$i."#" . $colour . ":'" . substr(str_pad($rrd['descr'], 18),0,18) . "':$stack";
      $rrd_options .= " GPRINT:".$rrd['rra'].$i.":LAST:%6.2lf%s$units";
      $rrd_options .= " GPRINT:".$rrd['rra'].$i."max:MAX:%6.2lf%s$units";
      if(!$nototal) { $rrd_options .= " GPRINT:tot".$rrd['rra'].$i.":%6.2lf%s$total_units"; }
      $rrd_options .= " COMMENT:'\\n'";
      $i++; $iter++;
  }

?>
