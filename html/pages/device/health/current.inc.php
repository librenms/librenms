<?php

$sql = "SELECT * FROM `sensors` WHERE sensor_class='current' AND device_id = '" . mres($_GET['id']) . "' ORDER BY sensor_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

while($current = mysql_fetch_array($query)) {

  if(!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=350>" . $current['sensor_descr'] . "</td>
          <td>" . $current['sensor_current'] . "A</td>
          <td>" . $current['sensor_limit_low'] . 'A - ' . $current['sensor_limit'] . "A</td>
          <td>" . $current['sensor_notes'] . "</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='4'>");

  $graph_type = "sensor_current";
  
  $graph_array['id'] = $current['sensor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");

  $i++;

}

echo("</table>");


?>

