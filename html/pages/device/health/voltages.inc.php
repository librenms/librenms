<?php

$sql = "SELECT * FROM `sensors` WHERE sensor_class='voltage' AND device_id = '" . $_GET[id] . "' ORDER BY sensor_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

while($volt = mysql_fetch_array($query)) {

  if(!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=350>" . $volt['sensor_descr'] . "</td>
          <td>" . $volt['sensor_current'] . "V</td>
          <td>" . $volt['sensor_limit_low'] . 'V - ' . $volt['sensor_limit'] . "V</td>
          <td>" . $volt['sensor_notes'] . "</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='4'>");

  $graph_type = "voltage";

// start voltage graphs

  $daily_volt   = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$day&to=$now&width=400&height=150";

  $weekly_volt  = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$week&to=$now&width=400&height=150";

  $monthly_volt = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$month&to=$now&width=400&height=150";

  $yearly_volt  = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $volt['sensor_id'] . "&type=$graph_type&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_volt' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_volt' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_volt' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_volt' border=0></a>");


  echo("</td></tr>");


  $row++;

}

echo("</table>");


?>

