<?php

$sql = "SELECT * FROM `sensors` WHERE device_id = '" . mres($_GET['id']) . "' AND `sensor_class` = 'freq' ORDER BY sensor_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

while($sensor = mysql_fetch_array($query)) {

  if(!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=350>" . $sensor['sensor_descr'] . "</td>
          <td>" . $sensor['sensor_current'] . "Hz</td>
          <td>" . $sensor['sensor_limit_low'] . 'Hz - ' . $sensor['sensor_limit'] . "Hz</td>
          <td>" . $sensor['sensor_notes'] . "</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='4'>");

  $graph_type = "sensor_frequency";

  $graph_array['id'] = $sensor['sensor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");


  echo("</td></tr>");


  $row++;

}

echo("</table>");


?>
