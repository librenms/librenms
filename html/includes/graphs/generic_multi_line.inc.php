<?php

  include("common.inc.php");

  $unit_text = str_pad($unit_text, 13);
  $unit_text = substr($unit_text,0,13);

  $i = 0;
  $iter = 0;

  $rrd_options .= " COMMENT:'".$unit_text."    Cur     Min     Max     Avg\\n'";


  foreach($rrd_list as $rrd) {
      if(!$config['graph_colours'][$colours][$iter]) { $iter = 0; }

      $colour=$config['graph_colours'][$colours][$iter];

      $rra = $rrd['rra'];
      $filename = $rrd['filename'];
      $descr = $rrd['descr'];
      $descr = substr(str_pad($descr, 10),0,10);
      $descr = str_replace(":", "\:", $descr);

      $id = $rra."_".$i;
      
      $rrd_options .= " DEF:".$id."=$filename:$rra:AVERAGE";
      $rrd_options .= " DEF:".$id."min=$filename:$rra:MIN";
      $rrd_options .= " DEF:".$id."max=$filename:$rra:MAX";
      $rrd_options .= " LINE1.25:".$id."#".$colour.":'$descr'";
      $rrd_options .= " GPRINT:".$id.":LAST:%5.2lf%s GPRINT:".$id."min:MIN:%5.2lf%s";
      $rrd_options .= " GPRINT:".$id."max:MAX:%5.2lf%s GPRINT:".$id.":AVERAGE:%5.2lf%s\\\\n";

      $i++; $iter++;
  }

?>
