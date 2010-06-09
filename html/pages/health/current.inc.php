<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `current` AS V, `devices` AS D WHERE V.device_id = D.device_id ORDER BY D.hostname, V.current_descr";
} else {
  $sql = "SELECT * FROM `current` AS V, `devices` AS D, devices_perms as P WHERE V.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, V.current_descr";
}

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="2" width="100%">');

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

  $weekly_current  = "graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $current_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_current\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $current['current_descr'] . "</a>";

  if($current['current_current'] >= $current['current_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $current_day    = "graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $current_week   = "graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $current_month  = "graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $current_year   = "graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $current_minigraph = "<img src='graph.php?id=" . $current['current_id'] . "&amp;type=current&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $current_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$current['hostname']." - ".$current['current_descr'];
  $current_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$current_day\'><img src=\'$current_week\'><img src=\'$current_month\'><img src=\'$current_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($current) . "</td>
          <td>$current_popup</td>
	  <td>$current_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $current['current_current'] . "A</td>
          <td style='text-align: center'>" . $current['current_limit_warn'] . "A</td>
          <td style='text-align: center'>" . $current['current_limit'] . "A</td>
          <td>" . (isset($current['current_notes']) ? $current['current_notes'] : '') . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>
