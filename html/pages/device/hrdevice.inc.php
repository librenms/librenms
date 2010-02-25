<?php

echo("<table width=100%>");

$hrdevices = mysql_query("SELECT * FROM `hrDevice` WHERE `device_id` = '".$device['device_id']."'");
while($hrdevice = mysql_fetch_array($hrdevices)) {

  echo("<tr><td>".$hrdevice['hrDeviceIndex']."</td>");

if($hrdevice['hrDeviceType'] == "hrDeviceProcessor") {
  $proc_id = mysql_result(mysql_query("SELECT processor_id FROM processors WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrdevice['hrDeviceIndex']."'"),0);
  $proc_url   = $config['base_url'] . "/device/".$device['device_id']."/health/processors/";
  $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$hrdevice['hrDeviceDescr'];
  $proc_popup .= "</div><img src=\'".$config['base_url']."/graph.php?id=" . $proc_id . "&type=processor&from=$month&to=$now&width=400&height=125\'>";
  $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";
  echo("<td><a href='$proc_url' $proc_popup>".$hrdevice['hrDeviceDescr']."</a></td>");
} elseif ($hrdevice['hrDeviceType'] == "hrDeviceNetwork") {
  $int = str_replace("network interface ", "", $hrdevice['hrDeviceDescr']);
  $interface = mysql_fetch_array(mysql_query("SELECT * FROM ports WHERE device_id = '".$device['device_id']."' AND ifDescr = '".$int."'")); 
  if($interface['ifIndex']) {
  echo("<td>".generateiflink($interface)."</td>");
  } else {
    echo("<td>".$hrdevice['hrDeviceDescr']."</td>");
  }
} else {
  echo("<td>".$hrdevice['hrDeviceDescr']."</td>");
}

  echo("<td>".$hrdevice['hrDeviceType']."</td><td>".$hrdevice['hrDeviceStatus']."</td>");
  echo("<td>".$hrdevice['hrDeviceErrors']."</td><td>".$hrdevice['hrProcessorLoad']."</td>");
  echo("</tr>");

}

echo("</table>");

?>

