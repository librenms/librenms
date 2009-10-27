<?php

  include("common.inc.php");

  $unit_text = str_pad($unit_text, 10);
  $unit_text = substr($unit_text,0,10);

  $i = 0;
  $iter = 0;

  $rrd_options .= " COMMENT:'".$unit_text."    Last    Min     Max     Avg\\n'";


  foreach($rrd_list as $rrd) {
      if(!$config['graph_colours'][$colours][$iter]) { $iter = 0; }

      $colour=$config['graph_colours'][$colours][$iter];

      $rra = $rrd['rra'];
      $filename = $rrd['filename'];
      $descr = $rrd['descr'];

      $id = $rra."_".$i;

      $rrd_options .= " DEF:".$id."=$filename:$rra:AVERAGE";
      $rrd_options .= " LINE1.25:".$id."#".$colour.":'$descr'";
      $rrd_options .= " GPRINT:".$id.":LAST:%6.2lf GPRINT:".$id.":AVERAGE:%6.2lf";
      $rrd_options .= " GPRINT:".$id.":MAX:%6.2lf GPRINT:".$id.":AVERAGE:%6.2lf\\\\n";

      $i++; $iter++;
  }

?>
