<?php

$graph_type = "sensor_humidity";

unset($humidity_seperator);
if($total = mysql_result(mysql_query("SELECT count(sensor_id) from sensors WHERE sensor_class='humidity' AND device_id = '" . $device['device_id'] . "'"),0)) {
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/humidity.png'> Humidity</p>");
  $i = '1';
  $humiditys = mysql_query("SELECT * FROM sensors WHERE sensor_class='humidity' AND device_id = '" . $device['device_id'] . "' ORDER BY sensor_index");
  echo('<table width="100%" valign="top">');
  echo('<tr><td width="50%">');
  echo('<table width="100%" cellspacing="0" cellpadding="2">');
  while($humidity = mysql_fetch_array($humiditys)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $humidity_perc = $humidity['sensor_current'] / $humidity['sensor_limit'] * 100;
    $humidity_colour = percent_colour($humidity_perc);
    $humidity_day    = "graph.php?id=" . $humidity['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=300&height=100";
    $humidity_week   = "graph.php?id=" . $humidity['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=300&height=100";
    $humidity_month  = "graph.php?id=" . $humidity['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=300&height=100";
    $humidity_year  = "graph.php?id=" . $humidity['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=300&height=100";
    $humidity_minigraph = "<img src='graph.php?id=" . $humidity['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $humidity_link  = "<a href='device/".$device['device_id']."/health/humiditys/' onmouseover=\"return ";
    $humidity_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$humidity['sensor_descr'];
    $humidity_link .= "</div><div style=\'width: 750px\'><img src=\'$humidity_day\'><img src=\'$humidity_week\'><img src=\'$humidity_month\'><img src=\'$humidity_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $humidity_link_c = $humidity_link . "<span style='color: $humidity_colour'>" . round($humidity['sensor_current'],0) . "%</span></a>";
    $humidity_link_b = $humidity_link . $humidity_minigraph . "</a>";
    $humidity_link_a = $humidity_link . $humidity['sensor_descr'] . "</a>";

    $humidity['sensor_descr'] = truncate($humidity['sensor_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$humidity_link_a</strong></td><td width=80 align=right class=tablehead>$humidity_link_b<td width=80 align=right class=tablehead>$humidity_link_c</td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
