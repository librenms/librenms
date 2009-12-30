<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `temperature` AS T, `devices` AS D WHERE T.temp_host = D.device_id ORDER BY D.hostname, T.temp_descr";
} else {
  $sql = "SELECT * FROM `temperature` AS T, `devices` AS D, devices_perms as P WHERE T.temp_host = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, T.temp_descr";
}

$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th width=280>Sensor</th>
        <th width=100>Current</th>
        <th width=100>Alert</th>
        <th>Notes</th>
      </tr>");

$row = 1;

while($temp = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($temp['ifSpeed']);
  $type = humanmedia($temp['ifType']);

  $weekly_temp  = "graph.php?id=" . $temp['temp_id'] . "&type=temp&from=$week&to=$now&width=211&height=100";
  $temp_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $temp['temp_descr'] . "</a>";

  $temp_perc = $temp['temp_current'] / $temp['temp_limit'] * 100;
  $temp_colour = percent_colour($temp_perc);



  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($temp) . "</td>
          <td>$temp_popup</td>
          <td style='color: $temp_colour; font-weight: bold;'>" . $temp['temp_current'] . "</td>
          <td>" . $temp['temp_limit'] . "</td>
          <td>" . $temp['temp_notes'] . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>

