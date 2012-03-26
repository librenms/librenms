<?php

echo('<table cellspacing="0" cellpadding="5" width="100%">');

$i=0;
foreach (dbFetchRows("SELECT * FROM `packages` WHERE `device_id` = ? ORDER BY `name`", array($device['device_id'])) as $entry)
{
  if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
  echo("<tr bgcolor=$row_colour>");
  echo('<td width=200><a href="'. generate_url($vars, array('name' => $entry['name'])).'">'.$entry['name'].'</a></td>');

  echo("<td>".$entry['version']."-".$entry['build']."</td>");

  echo("<td>".$entry['arch']."</td>");
  echo("<td>".$entry['manager']."</td>");
  echo("<td>".format_si($entry['size'])."</td>");


  echo("</tr>");

  $i++;

}

echo("</table>");

?>
