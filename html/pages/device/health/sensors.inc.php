<?php

$sql = "SELECT * FROM `sensors` WHERE sensor_class='".$class."' AND device_id = '" . mres($_GET['id']) . "' ORDER BY sensor_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

while ($temp = mysql_fetch_array($query))
{
  if (!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=500>" . $temp['sensor_descr'] . "</td>
          <td>" . $temp['sensor_type'] . "</td>
          <td width=50>" . format_si($temp['sensor_current']) .$unit. "</td>
          <td width=50>" . format_si($temp['sensor_limit']) . $unit . "</td>
          <td width=50>" . format_si($temp['sensor_limit_low']) . $unit ."</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='5'>");

  $graph_array['id'] = $temp['sensor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");

  echo("</td></tr>");

  $row++;
}

echo("</table>");

?>