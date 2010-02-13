<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `fanspeed` AS F, `devices` AS D WHERE F.device_id = D.device_id ORDER BY D.hostname, F.fan_descr";
} else {
  $sql = "SELECT * FROM `fanspeed` AS F, `devices` AS D, devices_perms as P WHERE F.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, F.fan_descr";
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

while($fan = mysql_fetch_array($query))
{
  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_fan  = "graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $fan_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_fan\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $fan['fan_descr'] . "</a>";

  if($fan['fan_current'] <= $fan['fan_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $fan_day    = "graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $fan_week   = "graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $fan_month  = "graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $fan_year   = "graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $fan_minigraph = "<img src='graph.php?id=" . $fan['fan_id'] . "&amp;type=fanspeed&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $fan_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$fan['hostname']." - ".$fan['fan_descr'];
  $fan_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$fan_day\'><img src=\'$fan_week\'><img src=\'$fan_month\'><img src=\'$fan_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($fan) . "</td>
          <td>$fan_popup</td>
	  <td>$fan_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $fan['fan_current'] . "  rpm</td>
          <td style='text-align: center'>" . $fan['fan_limit'] . "  rpm</td>
          <td>" . (isset($fan['fan_notes']) ? $fan['fan_notes'] : '') . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>

