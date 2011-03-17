<?php

$graph_type = "sensor_fanspeed";
$unit = "rpm";

if ($_SESSION['userlevel'] >= '5')
{
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D WHERE S.sensor_class='fanspeed' AND S.device_id = D.device_id ORDER BY D.hostname, S.sensor_descr";
} else {
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D, devices_perms as P WHERE S.sensor_class='fanspeed' AND S.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, S.sensor_descr";
}

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="2" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="280">Fan</th>
	<th></th>
	<th></th>
        <th width="100">Current</th>
        <th width="100">Alert</th>
        <th>Notes</th>
      </tr>');

$row = 1;

while ($sensor = mysql_fetch_array($query))
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_sensor = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $sensor_popup = "<a href=\"graphs/" . $sensor['sensor_id'] . "/".$graph_type."/\" onmouseover=\"return overlib('<img src=\'$weekly_sensor\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $sensor['sensor_descr'] . "</a>";

  if ($sensor['sensor_current'] <= $sensor['sensor_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }

  $sensor_day    = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $sensor_week   = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $sensor_month  = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $sensor_year   = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $sensor_minigraph = "<img src='graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $sensor_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$sensor['hostname']." - ".$sensor['sensor_descr'];
  $sensor_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$sensor_day\'><img src=\'$sensor_week\'><img src=\'$sensor_month\'><img src=\'$sensor_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generate_device_link($sensor) . "</td>
          <td>$sensor_popup</td>
	  <td>$sensor_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $sensor['sensor_current'] . $unit . "</td>
          <td style='text-align: center'>" . $sensor['sensor_limit'] . $unit . "</td>
          <td>" . (isset($sensor['sensor_notes']) ? $sensor['sensor_notes'] : '') . "</td>
        </tr>\n");

  if ($_GET['optb'] == "graphs")
  {

    echo("<tr bgcolor='$row_colour'><td colspan=6>");

    $daily_graph   = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=211&height=100";
    $daily_url       = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=400&height=150";

    $weekly_graph  = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=211&height=100";
    $weekly_url      = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=400&height=150";

    $monthly_graph = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=211&height=100";
    $monthly_url     = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=150";

    $yearly_graph  = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=211&height=100";
    $yearly_url  = "graph.php?id=" . $sensor['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=400&height=150";

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