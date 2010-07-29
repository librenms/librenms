<?php

$graph_type = "sensor_frequency";

unset($freq_seperator);
if(mysql_result(mysql_query("SELECT count(freq_id) from frequency WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  $total = mysql_result(mysql_query("SELECT count(freq_id) from frequency WHERE device_id = '" . $device['device_id'] . "'"),0);
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/frequencies.png'> Frequencies</p>");
  $i = '1';
  $freqs = mysql_query("SELECT * FROM frequency WHERE device_id = '" . $device['device_id'] . "'");
  echo("<table width=100% valign=top>");
  echo("<tr><td width=50%>");
  echo("<table width=100% cellspacing=0 cellpadding=2>");
  while($freq = mysql_fetch_array($freqs)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $freq_day    = "graph.php?id=" . $freq['freq_id'] . "&type=".$graph_type."&from=$day&to=$now&width=300&height=100";
    $freq_week   = "graph.php?id=" . $freq['freq_id'] . "&type=".$graph_type."&from=$week&to=$now&width=300&height=100";
    $freq_month  = "graph.php?id=" . $freq['freq_id'] . "&type=".$graph_type."&from=$month&to=$now&width=300&height=100";
    $freq_year  = "graph.php?id=" . $freq['freq_id'] . "&type=".$graph_type."&from=$year&to=$now&width=300&height=100";
    $freq_minigraph = "<img src='graph.php?id=" . $freq['freq_id'] . "&type=".$graph_type."&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $freq_link  = "<a href='device/".$device['device_id']."/health/frequencies/' onmouseover=\"return ";
    $freq_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$freq['freq_descr'];
    $freq_link .= "</div><div style=\'width: 750px\'><img src=\'$freq_day\'><img src=\'$freq_week\'><img src=\'$freq_month\'><img src=\'$freq_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $freq_link_c = $freq_link . "<span " . ($freq['freq_current'] < $freq['freq_limit_low'] || $freq['freq_current'] > $freq['freq_limit'] ? "style='color: red'" : '') . '>' . $freq['freq_current'] . "Hz</span></a>";
    $freq_link_b = $freq_link . $freq_minigraph . "</a>";
    $freq_link_a = $freq_link . $freq['freq_descr'] . "</a>";

    $freq['freq_descr'] = truncate($freq['freq_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$freq_link_a</strong></td><td width=80 align=right class=tablehead>$freq_link_b<td width=80 align=right class=tablehead>$freq_link_c</td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
