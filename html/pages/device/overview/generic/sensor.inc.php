<?php

$results = mysql_query("SELECT * FROM sensors WHERE sensor_class='" . $sensor_class . "' AND device_id = '" . $device['device_id'] . "' ORDER BY sensor_index");

if(mysql_num_rows($results))
{
  $rows = round(mysql_num_rows($results) / 2,0);
  echo('<div style="background-color: #eeeeee; margin: 5px; padding: 5px;">');
  echo('<p style="padding: 0px 5px 5px;" class="sectionhead"><a class="sectionhead" href="device/'.$device['device_id'].'/health/' . strtolower($sensor_type) . '/"><img align="absmiddle" src="'.$config['base_url'].'/images/icons/' . strtolower($sensor_type) . '.png"> ' . $sensor_type . '</p>');
  $i = '1';
  echo('<table width="100%" valign="top">');
  while($sensor = mysql_fetch_array($results)) 
  {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $graph_colour = str_replace("#", "", $row_colour);

    $sensor_day    = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=300&height=100";
    $sensor_week   = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=300&height=100";
    $sensor_month  = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=300&height=100";
    $sensor_year   = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=300&height=100";
    $sensor_minigraph = "<img src='graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=80&height=20&bg=$graph_colour' align='absmiddle'>";

    $sensor_link  = "<a href='graphs/".$sensor['sensor_id']."/" . $graph_type . "/' onmouseover=\"return ";
    $sensor_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$sensor['sensor_descr'];
    $sensor_link .= "</div><div style=\'width: 750px\'><img src=\'$sensor_day\'><img src=\'$sensor_week\'><img src=\'$sensor_month\'><img src=\'$sensor_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

    $sensor_link_c = $sensor_link . "<span " . ($sensor['sensor_current'] < $sensor['sensor_limit_low'] || $sensor['sensor_current'] > $sensor['sensor_limit'] ? "style='color: red'" : '') . '>' . $sensor['sensor_current'] . $sensor_unit . "</span></a>";
    $sensor_link_b = $sensor_link . $sensor_minigraph . "</a>";
    $sensor_link_a = $sensor_link . $sensor['sensor_descr'] . "</a>";

    $sensor['sensor_descr'] = truncate($sensor['sensor_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td class=tablehead><strong>$sensor_link_a</strong></td><td width=80 align=right class=tablehead>$sensor_link_b<td width=80 align=right class=tablehead>$sensor_link_c</td></tr>");
    $i++;
  }
  echo("</table>");
  echo("</div>");
}

?>
