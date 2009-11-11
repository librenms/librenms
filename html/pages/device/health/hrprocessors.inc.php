<?php

  echo("<div style='margin-top: 5px; padding: 0px;'>");
  echo("<table width=100% cellpadding=6 cellspacing=0>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `hrDevice` WHERE device_id = '" . $device['device_id'] . "' AND hrDeviceType = 'hrDeviceProcessor'");
  while($proc = mysql_fetch_array($procs)) {

    $proc_url   = "?page=device/".$device['device_id']."/health/hrprocessors/";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['entPhysicalDescr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    if($proc['cpuCPMTotal5minRev'] > '60') { $proc_colour='#cc0000'; } else { $proc_colour='#0000cc';  }
    echo("<tr><td class=tablehead width=350><a href='' $proc_popup>" . $proc['hrDeviceDescr'] . "</a></td>
           <td><a href='#' $proc_popup><img src='percentage.php?per=" . $proc['hrProcessorLoad'] . "&width=600'></a></td>
           <td style='font-weight: bold; color: $proc_colour'>" . $proc['hrProcessorLoad'] . "%</td>
         </tr>");
 
  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $daily_graph   = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$day&to=$now&width=211&height=100";
  $daily_url     = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$week&to=$now&width=211&height=100";
  $weekly_url    = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$month&to=$now&width=211&height=100";
  $monthly_url   = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$year&to=$now&width=211&height=100";
  $yearly_url    = "graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
  echo("</td></tr>");

    $i++;
  }
  echo("</table>");
  echo("</div>");


?>
