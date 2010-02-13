<?php

if(mysql_result(mysql_query("SELECT count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  $processor_rows = 0;
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>Processors</p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `processors` WHERE device_id = '" . $device['device_id'] . "'");
  while($proc = mysql_fetch_array($procs)) {
    if(is_integer($processor_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $proc_url   = $config['base_url'] . "/device/".$device['device_id']."/health/processors/";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['processor_descr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $mini_url = $config['base_url'] . "/graph.php?id=".$proc['processor_id']."&type=processor&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

    $text_descr = $proc['processor_descr'];
#    $text_descr = short_processor_descr($proc['processor_descr']);

    if($proc['processor_usage'] > '60') { $proc_colour='#cc0000'; } else { $proc_colour='#0000cc';  }
    echo("<tr bgcolor=$row_colour><td class=tablehead width=350><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
           <td width=100><a href='".$proc_url."'><img src='$mini_url'></a></td>
           <td width=160><a href='".$proc_url."' $proc_popup>
             <img src='percentage.php?per=" . $proc['processor_usage'] . "&width=150'></a></td>
           <td style='font-weight: bold; color: $proc_colour'>
           " . $proc['processor_usage'] . "%</td>
         </tr>");
    $processor_rows++;    
  }
  echo("</table>");
  echo("</div>");
}

?>
