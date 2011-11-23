<?php

if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

echo("<tr bgcolor='$bg_colour'>");

echo("<td width=100 class=list-large> Vlan " . $vlan['vlan_vlan'] . "</td>");
echo("<td width=200 class=box-desc>" . $vlan['vlan_descr'] . "</td>");
echo("<td class=list-bold>");

foreach (dbFetchRows("SELECT * FROM ports WHERE `device_id` = ? AND `ifVlan` = ?", array($device['device_id'], $vlan['vlan_vlan'])) as $port)
{
  $port = ifLabel($port, $device);
  if ($vars['view'] == "graphs")
  {
    echo("<div style='display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: ".$list_colour_b_b.";'>
    <div style='font-weight: bold;'>".makeshortif($port['ifDescr'])."</div>
    <a href='device/device=".$device['device_id']."/tab=port/port=".$port['interface_id']."/' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=$graph_type&amp;id=".$port['interface_id']."&amp;from=-2day&amp;to=".$now."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&amp;id=".$port['interface_id']."&amp;from=-2day&amp;to=".$now."&amp;width=132&amp;height=40&amp;legend=no'>
    </a>
    <div style='font-size: 9px;'>".truncate(short_port_descr($port['ifAlias']), 22, '')."</div>
   </div>");
  }
  else
  {
    echo($vlan['port_sep'] . generate_port_link($port, makeshortif($port['label']));
    $vlan['port_sep'] = ", ";
  }
}

echo('</td></tr>');

?>
