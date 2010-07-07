<?php

unset($volt_seperator);
if($total = mysql_result(mysql_query("SELECT count(sensor_id) from sensors WHERE sensor_class='voltage' AND device_id = '" . $device['device_id'] . "'"),0)) {
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/voltages.png'> Voltages</p>");
  $i = '1';
  $volts = mysql_query("SELECT * FROM sensors WHERE sensor_class='voltage' AND device_id = '" . $device['device_id'] . "'");
  echo("<table width=100% valign=top>");
  echo("<tr><td width=50%>");
  echo("<table width=100% cellspacing=0 cellpadding=2>");
  while($volt = mysql_fetch_array($volts)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $volt_day    = "graph.php?id=" . $volt['sensor_id'] . "&type=voltage&from=$day&to=$now&width=300&height=100";
    $volt_week   = "graph.php?id=" . $volt['sensor_id'] . "&type=voltage&from=$week&to=$now&width=300&height=100";
    $volt_month  = "graph.php?id=" . $volt['sensor_id'] . "&type=voltage&from=$month&to=$now&width=300&height=100";
    $volt_year  = "graph.php?id=" . $volt['sensor_id'] . "&type=voltage&from=$year&to=$now&width=300&height=100";
    $volt_minigraph = "<img src='graph.php?id=" . $volt['sensor_id'] . "&type=voltage&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $volt_link  = "<a href='/device/".$device['device_id']."/health/voltages/' onmouseover=\"return ";
    $volt_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$volt['sensor_descr'];
    $volt_link .= "</div><div style=\'width: 750px\'><img src=\'$volt_day\'><img src=\'$volt_week\'><img src=\'$volt_month\'><img src=\'$volt_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $volt_link_c = $volt_link . "<span " . ($volt['sensor_current'] < $volt['sensor_limit_low'] || $volt['sensor_current'] > $volt['sensor_limit'] ? "style='color: red'" : '') . '>' . $volt['sensor_current'] . "V</span></a>";
    $volt_link_b = $volt_link . $volt_minigraph . "</a>";
    $volt_link_a = $volt_link . $volt['sensor_descr'] . "</a>";

    $volt['sensor_descr'] = truncate($volt['sensor_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$volt_link_a</strong></td><td width=80 align=right class=tablehead>$volt_link_b<td width=80 align=right class=tablehead>$volt_link_c</td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
