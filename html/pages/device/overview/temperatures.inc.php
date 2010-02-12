<?php

unset($temp_seperator);
if(mysql_result(mysql_query("SELECT count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
  $total = mysql_result(mysql_query("SELECT count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"),0);
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Temperatures</p>");
  $i = '1';
  $temps = mysql_query("SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'");
  echo("<table width=100% valign=top>");
  echo("<tr><td width=50%>");
  echo("<table width=100% cellspacing=0 cellpadding=2>");
  while($temp = mysql_fetch_array($temps)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $temp_perc = $temp['temp_current'] / $temp['temp_limit'] * 100;
    $temp_colour = percent_colour($temp_perc);
    $temp_day    = "graph.php?id=" . $temp['temp_id'] . "&type=temperature&from=$day&to=$now&width=300&height=100";
    $temp_week   = "graph.php?id=" . $temp['temp_id'] . "&type=temperature&from=$week&to=$now&width=300&height=100";
    $temp_month  = "graph.php?id=" . $temp['temp_id'] . "&type=temperature&from=$month&to=$now&width=300&height=100";
    $temp_year  = "graph.php?id=" . $temp['temp_id'] . "&type=temperature&from=$year&to=$now&width=300&height=100";
    $temp_minigraph = "<img src='graph.php?id=" . $temp['temp_id'] . "&type=temperature&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $temp_link  = "<a href='/device/".$device['device_id']."/health/temp/' onmouseover=\"return ";
    $temp_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$temp['temp_descr'];
    $temp_link .= "</div><div style=\'width: 750px\'><img src=\'$temp_day\'><img src=\'$temp_week\'><img src=\'$temp_month\'><img src=\'$temp_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $temp_link_c = $temp_link . "<span style='color: $temp_colour'>" . $temp['temp_current'] . "&deg;C</span></a>";
    $temp_link_b = $temp_link . $temp_minigraph . "</a>";
    $temp_link_a = $temp_link . $temp['temp_descr'] . "</a>";

    $temp['temp_descr'] = truncate($temp['temp_descr'], 25, '');
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
