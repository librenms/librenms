<?php

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

$sql = "SELECT * FROM pseudowires AS P, interfaces AS I, devices AS D WHERE P.interface_id = I.interface_id AND I.device_id = D.device_id ORDER BY D.hostname,I.ifDescr";
$query = mysql_query($sql);

while($pw_a = mysql_fetch_array($query)) {
   $i = 0;
   while ($i < count($linkdone)) {
      $thislink = $pw_a['device_id'] . $pw_a['interface_id'];
      if ($linkdone[$i] == $thislink) { $skip = "yes"; }
      $i++;
  }

  $pw_b = mysql_fetch_array(mysql_query("SELECT * from `devices` AS D, `interfaces` AS I, `pseudowires` AS P WHERE D.device_id = '".$pw_a['peer_device_id']."' AND
                                                                                                          D.device_id = I.device_id AND
                                                                                                          P.cpwVcID = '".$pw_a['cpwVcID']."' AND
                                                                                                          P.interface_id = I.interface_id"));

  if(!interfacepermitted($pw_a['interface_id'])) { $skip = "yes"; }
  if(!interfacepermitted($pw_b['interface_id'])) { $skip = "yes"; }


  if($skip) {
    unset($skip);
  } else {
    if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

    echo("<tr style=\"background-color: $bg;\"><td rowspan=2 style='font-size:18px; padding:4px;'>".$pw_a['cpwVcID']."</td><td>".generatedevicelink($pw_a)."</td><td>".generateiflink($pw_a)."</td>
                                                                                          <td rowspan=2> => </td>
                                                                                          <td>".generatedevicelink($pw_b)."</td><td>".generateiflink($pw_b)."</td></tr>");
    echo("<tr style=\"background-color: $bg;\"><td colspan=2>".$pw_a['ifAlias']."</td><td colspan=2>".$pw_b['ifAlias']."</td></tr>");
    $linkdone[] = $pw_b['device_id'] . $pw_b['interface_id'];
  }
}

echo("</table>");

?>
