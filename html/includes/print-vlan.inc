<?php

if(is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

echo("<tr bgcolor='$bg_colour'>");

echo("<td  class=list-large> Vlan " . $vlan['vlan_vlan'] . "</td>");
echo("<td class=box-desc>" . $vlan['vlan_descr'] . "</td>");

echo("<td class=list-bold>");
$ports_query = mysql_query("SELECT * FROM interfaces WHERE `device_id` = '" . $device['device_id'] . "' AND `ifVlan` = '" . $vlan['vlan_vlan'] . "' ");
while($port = mysql_fetch_array($ports_query)) {

  echo($vlan['port_sep'] . generateiflink($port, makeshortif($port['ifDescr'])));
  $vlan['port_sep'] = ", ";
}
echo("</td>");
echo("</tr>");

?>
