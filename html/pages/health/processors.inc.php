<?php

  echo("<div style='margin-top: 5px; padding: 0px;'>");
  echo("  <table width=100% cellpadding=6 cellspacing=0>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `processors` AS P, `devices` AS D WHERE D.device_id = P.device_id ORDER BY D.hostname");
  while($proc = mysql_fetch_array($procs)) {

   if(devicepermitted($proc['device_id'])) { 

    $device = $proc;

    $text_descr = $proc['processor_descr'];
    $text_descr = str_replace("Routing Processor", "RP", $text_descr);
    $text_descr = str_replace("Switching Processor", "SP", $text_descr);
    $text_descr = str_replace("Sub-Module", "Module ", $text_descr);
    $text_descr = str_replace("DFC Card", "DFC", $text_descr);


    $proc_url   = "?page=device/".$device['device_id']."/health/processors/";

    $mini_url = $config['base_url'] . "/graph.php?id=".$proc['processor_id']."&type=processor&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['proc_id'] . "&type=proc&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $perc = round($proc['processor_usage']);

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }

    echo("    <tr bgcolor=\"$row_colour\">
               <td>".generatedevicelink($proc)."</td>
               <td class=\"tablehead\"><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
               <td width=\"90\"><a href=\"".$proc_url."\"  $proc_popup><img src=\"$mini_url\" /></a></td>
               <td width=\"200\"><a href=\"".$proc_url."\" $proc_popup>
           ".print_percentage_bar (400, 20, $perc, $perc."%", "ffffff", $left_background, (100 - $perc)."%" , "ffffff", $right_background).'</a></td>
             </tr>');
 
  echo('    <tr bgcolor="'.$row_colour.'"><td colspan="5">');

  $daily_graph   = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$day&to=$now&width=211&height=100";
  $daily_url     = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$week&to=$now&width=211&height=100";
  $weekly_url    = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$month&to=$now&width=211&height=100";
  $monthly_url   = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$year&to=$now&width=211&height=100";
  $yearly_url    = "graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$year&to=$now&width=400&height=150";

  echo("      <a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src="$daily_graph" border=\"0\"></a> ");
  echo("      <a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src="$weekly_graph" border=\"0\"></a> ");
  echo("      <a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src="$monthly_graph" border=\"0\"></a> ");
  echo("      <a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src=\"$yearly_graph\" border=\"0\"></a>");
  echo("  </td>
  </tr>");

    $i++;
   }

  }
  echo("</table>");
  echo("</div>");


?>
