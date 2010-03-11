<?php

unset($fan_seperator);
if(mysql_result(mysql_query("SELECT count(fan_id) from fanspeed WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  $total = mysql_result(mysql_query("SELECT count(fan_id) from fanspeed WHERE device_id = '" . $device['device_id'] . "'"),0);
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/fans.png'> Fanspeeds</p>");
  $i = '1';
  $fans = mysql_query("SELECT * FROM fanspeed WHERE device_id = '" . $device['device_id'] . "'");
  echo("<table width=100% valign=top>");
  echo("<tr><td width=50%>");
  echo("<table width=100% cellspacing=0 cellpadding=2>");
  while($fan = mysql_fetch_array($fans)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $fan_day    = "graph.php?id=" . $fan['fan_id'] . "&type=fanspeed&from=$day&to=$now&width=300&height=100";
    $fan_week   = "graph.php?id=" . $fan['fan_id'] . "&type=fanspeed&from=$week&to=$now&width=300&height=100";
    $fan_month  = "graph.php?id=" . $fan['fan_id'] . "&type=fanspeed&from=$month&to=$now&width=300&height=100";
    $fan_year  = "graph.php?id=" . $fan['fan_id'] . "&type=fanspeed&from=$year&to=$now&width=300&height=100";
    $fan_minigraph = "<img src='graph.php?id=" . $fan['fan_id'] . "&type=fanspeed&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $fan_link  = "<a href='/device/".$device['device_id']."/health/fanspeeds/' onmouseover=\"return ";
    $fan_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$fan['fan_descr'];
    $fan_link .= "</div><div style=\'width: 750px\'><img src=\'$fan_day\'><img src=\'$fan_week\'><img src=\'$fan_month\'><img src=\'$fan_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $fan_link_b = $fan_link . $fan_minigraph . "</a>";
    $fan_link_c = $fan_link . "<span style='" . ($fan['fan_current'] <= $fan['fan_limit'] ? "color: red" : '') . "'>" . $fan['fan_current'] . "rpm</span></a>";
    $fan_link_a = $fan_link . $fan['fan_descr'] . "</a>";

    $fan['fan_descr'] = truncate($fan['fan_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$fan_link_a</strong></td><td width=80 align=right class=tablehead>$fan_link_b<td width=80 align=right class=tablehead>$fan_link_c</td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
