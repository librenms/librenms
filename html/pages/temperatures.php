<?php

$sql = "SELECT * FROM `temperature` AS T, `devices` AS D WHERE T.temp_host = D.device_id ORDER BY D.hostname, T.temp_descr";
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

  $temp_url    = "graph.php?id=" . $temp['temp_id'] . "&type=temp&from=$week&to=$now&width=400&height=150";

  $temp_popup = "<a onmouseover=\"return overlib('<img src=\'$temp_url\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $temp['temp_descr'] . "</a> ";



  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($temp) . "</td>
          <td>$temp_popup</td>
          <td>" . print_temperature($temp['temp_current'], $temp['temp_limit']) . "</td>
          <td>" . $temp['temp_limit'] . "</td>
          <td>" . $temp['temp_notes'] . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>

