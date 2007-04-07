<?php

$sql = "SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.id ORDER BY D.hostname, I.ifDescr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr class=tablehead><th width=280>Device</a></th><th>Interface</th><th>Speed</th><th>Media</th><th>Description</th></tr>");

$row = 1;

while($interface = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($interface['ifSpeed']);
  $if_link = generateiflink($interface);
  $dev_link = generatedevicelink($interface);
  $type = humanmedia($interface['ifType']);

  echo("<tr bgcolor=$row_colour><td><a href='' class=list-bold>$dev_link</a></td><td class=list-bold>$if_link</td><td>$speed</td><td>$type</td><td>" . $interface[ifAlias] . "</td></tr>\n");

  $row++;

}

echo("</table>");


?>

