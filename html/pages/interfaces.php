<?php

if ($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id ORDER BY D.hostname, I.ifDescr";
} else {
  $sql = "SELECT * FROM `interfaces` AS I, `devices` AS D, `devices_perms` AS P WHERE I.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, I.ifDescr";
}

$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr class=tablehead><th width=280>Device</a></th><th>Interface</th><th>Speed</th><th>Media</th><th>Description</th></tr>");

$row = 1;

while($interface = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($interface['ifSpeed']);
  $type = humanmedia($interface['ifType']);

    if($interface['in_errors'] > 0 || $interface['out_errors'] > 0) {
    $error_img = generateiflink($interface,"<img src='/images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
  } else { $error_img = ""; }


  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($interface) . "</td>
          <td class=list-bold>" . generateiflink($interface, makeshortif(fixifname($interface['ifDescr']))) . " $error_img</td>
          <td>$speed</td>
          <td>$type</td>
          <td>" . $interface[ifAlias] . "</td>
        </tr>\n");

  $row++;

}

echo("</table>");


?>

