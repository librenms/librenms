<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D WHERE S.sensor_class='current' AND S.device_id = D.device_id ORDER BY D.hostname, S.sensor_descr";
} else {
  $sql = "SELECT * FROM `current` AS S, `devices` AS D, devices_perms as P WHERE S.sensor_class='current' AND S.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, S.sensor_descr";
}

$graph_type = "sensor_current";

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="6" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="180">Sensor</th>
	<th></th>
	<th></th>
        <th width="100">Current</th>
        <th width="100">Warning</th>
        <th width="100">Limit</th>
        <th>Notes</th>
      </tr>');

$row = 1;

while($current = mysql_fetch_array($query))
{
  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_current  = "graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $current_popup = "<a href=\"graphs/" . $current['sensor_id'] . "/".$graph_type."/\" onmouseover=\"return overlib('<img src=\'$weekly_current\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $current['sensor_descr'] . "</a>";

  if($current['sensor_current'] >= $current['sensor_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $current_day    = "graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $current_week   = "graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $current_month  = "graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $current_year   = "graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $current_minigraph = "<img src='graph.php?id=" . $current['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $current_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$current['hostname']." - ".$current['sensor_descr'];
  $current_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$current_day\'><img src=\'$current_week\'><img src=\'$current_month\'><img src=\'$current_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generate_device_link($current) . "</td>
          <td>$current_popup</td>
	  <td>$current_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $current['sensor_current'] . "A</td>
          <td style='text-align: center'>" . $current['sensor_limit_warn'] . "A</td>
          <td style='text-align: center'>" . $current['sensor_limit'] . "A</td>
          <td>" . (isset($current['sensor_notes']) ? $current['sensor_notes'] : '') . "</td>
        </tr>\n");

      if($_GET['optb'] == "graphs") { ## If graphs

  echo("<tr bgcolor='$row_colour'><td colspan=7>");

  $daily_graph   = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $current['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
  echo("</td></tr>");

    } # endif graphs


  $row++;

}

echo("</table>");


?>
