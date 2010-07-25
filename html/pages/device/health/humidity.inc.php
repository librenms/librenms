<?php

$sql = "SELECT * FROM `sensors` WHERE sensor_class='humidity' AND device_id = '" . mres($_GET['id']) . "' ORDER BY sensor_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

while($humidity = mysql_fetch_array($query)) {

  if(!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=350>" . $humidity['sensor_descr'] . "</td>
          <td>" . $humidity['sensor_current'] . " %</td>
          <td>" . $humidity['sensor_limit'] . " %</td>
          <td>" . $humidity['sensor_notes'] . "</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='4'>");

  $graph_type = "sensor_humidity";

  $graph_array['id'] = $humidity['sensor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");


  echo("</td></tr>");


  $row++;

}

echo("</table>");


?>

