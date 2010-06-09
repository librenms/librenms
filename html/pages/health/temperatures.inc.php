<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `temperature` AS T, `devices` AS D WHERE T.device_id = D.device_id ORDER BY D.hostname, T.temp_index, T.temp_descr";
} else {
  $sql = "SELECT * FROM `temperature` AS T, `devices` AS D, devices_perms as P WHERE T.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, T.temp_index, T.temp_descr";
}

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="2" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="280">Sensor</th>
	<th></th>
	<th></th>
        <th width="100">Current</th>
        <th width="100">Alert</th>
        <th>Notes</th>
      </tr>');

$row = 1;

while($temp = mysql_fetch_array($query))
{
  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_temp  = "graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $temp_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_temp\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $temp['temp_descr'] . "</a>";

  $temp_perc = $temp['temp_current'] / $temp['temp_limit'] * 100;
  $temp_colour = percent_colour($temp_perc);

  if($temp['temp_current'] >= $temp['temp_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $temp_day    = "graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $temp_week   = "graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $temp_month  = "graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $temp_year   = "graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $temp_minigraph = "<img src='graph.php?id=" . $temp['temp_id'] . "&amp;type=temperature&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $temp_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$temp['hostname']." - ".$temp['temp_descr'];
  $temp_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$temp_day\'><img src=\'$temp_week\'><img src=\'$temp_month\'><img src=\'$temp_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($temp) . "</td>
          <td>$temp_popup</td>
	  <td>$temp_minigraph</td>
	  <td width=100>$alert</td>
          <td style='color: $temp_colour; text-align: center; font-weight: bold;'>" . $temp['temp_current'] . " &deg;C</td>
          <td style='text-align: center'>" . $temp['temp_limit'] . " &deg;C</td>
          <td>" . (isset($temp['temp_notes']) ? $temp['temp_notes'] : '') . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>

