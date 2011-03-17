<?php

if (is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

echo("<tr bgcolor='$bg_colour'>");

echo("<td width=200 class=list-large><a href='vrf/".$vrf['mplsVpnVrfRouteDistinguisher']."/'>" . $vrf['vrf_name'] . "</a></td>");
echo("<td width=150 class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");
echo("<td width=100 class=box-desc>" . $vrf['mplsVpnVrfRouteDistinguisher'] . "</td>");


echo("<td class=list-bold>");
$ports_query = mysql_query("SELECT * FROM ports WHERE `device_id` = '" . $device['device_id'] . "' AND `ifVrf` = '" . $vrf['vrf_id'] . "' ");
while ($port = mysql_fetch_array($ports_query)) {
  if ($_GET['opta']) {
   $graph_type = "port_" . $_GET['opta'];
   echo("<div style='display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".makeshortif($port['ifDescr'])."</div>
    <a href='device/".$device['device_id']."/interface/".$port['interface_id']."/' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=$graph_type&if=".$port['interface_id']."&from=-2day&to=".$now."&width=450&height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&if=".$port['interface_id']."&from=-2day&to=".$now."&width=132&height=40&legend=no'>
    </a>
    <div style='font-size: 9px;'>".truncate(short_port_descr($port['ifAlias']), 22, '')."</div>
   </div>");
  } else {
    echo($vrf['port_sep'] . generate_port_link($port, makeshortif($port['ifDescr'])));
    $vrf['port_sep'] = ", ";
  }
}
echo("</td>");
echo("</tr>");

?>
