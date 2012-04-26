<?php

echo("<table cellspacing=0 cellpadding=5 width=100%>");

$row = 1;

foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`", array($class, $device['device_id'])) as $sensor)
{
  if (!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=list-large style=\"background-color: $row_colour; padding: 5px;\">
          <td width=500>" . $sensor['sensor_descr'] . "</td>
          <td>" . $sensor['sensor_type'] . "</td>
          <td width=50>" . format_si($sensor['sensor_current']) .$unit. "</td>
          <td width=50>" . format_si($sensor['sensor_limit']) . $unit . "</td>
          <td width=50>" . format_si($sensor['sensor_limit_low']) . $unit ."</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='5'>");

  $graph_array['id'] = $sensor['sensor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-graphrow.inc.php");

  echo("</td></tr>");

  $row++;
}

echo("</table>");

?>
