<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `frequency` AS V, `devices` AS D WHERE V.device_id = D.device_id ORDER BY D.hostname, V.freq_descr";
} else {
  $sql = "SELECT * FROM `frequency` AS V, `devices` AS D, devices_perms as P WHERE V.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, V.freq_descr";
}

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="2" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="180">Sensor</th>
	<th></th>
	<th></th>
        <th width="100">Current</th>
        <th width="250">Range limit</th>
        <th>Notes</th>
      </tr>');

$row = 1;

while($freq = mysql_fetch_array($query))
{
  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_freq  = "graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $freq_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_freq\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $freq['freq_descr'] . "</a>";

  if($freq['freq_current'] >= $freq['freq_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $freq_day    = "graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $freq_week   = "graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $freq_month  = "graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $freq_year   = "graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $freq_minigraph = "<img src='graph.php?id=" . $freq['freq_id'] . "&amp;type=frequency&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $freq_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$freq['hostname']." - ".$freq['freq_descr'];
  $freq_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$freq_day\'><img src=\'$freq_week\'><img src=\'$freq_month\'><img src=\'$freq_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($freq) . "</td>
          <td>$freq_popup</td>
	  <td>$freq_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $freq['freq_current'] . "Hz</td>
          <td style='text-align: center'>" . $freq['freq_limit_low'] . "Hz - " . $freq['freq_limit'] . "Hz</td>
          <td>" . (isset($freq['freq_notes']) ? $freq['freq_notes'] : '') . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>
