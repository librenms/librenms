<?php

if(is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

echo("<tr bgcolor='$bg_colour'>");

echo("<td  class=list-large><a href='vrf/".$vrf['mplsVpnVrfRouteDistinguisher']."/'>" . $vrf['vrf_name'] . "</a></td>");
echo("<td class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");
echo("<td class=box-desc>" . $vrf['mplsVpnVrfRouteDistinguisher'] . "</td>");


echo("<td class=list-bold>");
$ports_query = mysql_query("SELECT * FROM interfaces WHERE `device_id` = '" . $device['device_id'] . "' AND `ifVrf` = '" . $vrf['vrf_id'] . "' ");
while($port = mysql_fetch_array($ports_query)) {

  echo($vrf['port_sep'] . generateiflink($port, makeshortif($port['ifDescr'])));
  $vrf['port_sep'] = ", ";
}
echo("</td>");
echo("</tr>");

?>
