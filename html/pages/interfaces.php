<?php

$sql = "SELECT *, D.id as dev_id, I.id as id FROM `interfaces` AS I, `devices` AS D WHERE I.host = D.id ORDER BY D.hostname, I.if";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr class=tablehead><th width=280>Device</a></th><th>Interface</th><th>Speed</th><th>Media</th><th>Description</th></tr>");

$row = 1;

while($iface = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($iface['ifSpeed']);
  $if_link = generateiflink($iface);
  $dev_link = generatedevicelink($iface);
  $type = humanmedia($iface['ifType']);

  echo("<tr bgcolor=$row_colour><td><a href='' class=list-bold>$dev_link</a></td><td class=list-bold>$if_link</td><td>$speed</td><td>$type</td><td>$iface[name]</td></tr>\n");

  $row++;

}

echo("</table>");


?>

