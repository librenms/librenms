<?php

$graph_type = "sensor_temperature";

if($total = mysql_result(mysql_query("SELECT count(sensor_id) from sensors WHERE sensor_class='temperature' AND device_id = '" . $device['device_id'] . "'"),0)) {
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/temperatures.png'> Temperatures</p>");
  $i = '1';
  $temps = mysql_query("SELECT * FROM sensors WHERE sensor_class='temperature' AND device_id = '" . $device['device_id'] . "' ORDER BY sensor_index");
  echo('<table width="100%" valign="top">');
  echo('<tr><td width="50%">');
  echo('<table width="100%" cellspacing="0" cellpadding="2">');
  while($temp = mysql_fetch_array($temps)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $temp_perc = $temp['sensor_current'] / $temp['sensor_limit'] * 100;
    $temp_colour = percent_colour($temp_perc);
    $temp_day    = "graph.php?id=" . $temp['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=300&height=100";
    $temp_week   = "graph.php?id=" . $temp['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=300&height=100";
    $temp_month  = "graph.php?id=" . $temp['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=300&height=100";
    $temp_year  = "graph.php?id=" . $temp['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=300&height=100";
    $temp_minigraph = "<img src='graph.php?id=" . $temp['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $temp_link  = "<a href='device/".$device['device_id']."/health/temperatures/' onmouseover=\"return ";
    $temp_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$temp['sensor_descr'];
    $temp_link .= "</div><div style=\'width: 750px\'><img src=\'$temp_day\'><img src=\'$temp_week\'><img src=\'$temp_month\'><img src=\'$temp_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $temp_link_c = $temp_link . "<span style='color: $temp_colour'>" . round($temp['sensor_current'],0) . "&deg;C</span></a>";
    $temp_link_b = $temp_link . $temp_minigraph . "</a>";
    $temp_link_a = $temp_link . $temp['sensor_descr'] . "</a>";

    $temp['sensor_descr'] = truncate($temp['sensor_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$temp_link_a</strong></td><td width=80 align=right class=tablehead>$temp_link_b<td width=80 align=right class=tablehead>$temp_link_c</td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
