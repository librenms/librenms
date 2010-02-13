<?php

if($interfaces['total']) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>
        " . device_traffic_image($device['device_id'], 490, 100, $day, '-300s') . "");

  echo("<div style='height: 5px;'></div>");

echo("
<table class=tablehead cellpadding=2 cellspacing=0 width=100%>
<tr bgcolor=$interfaces_colour align=center><td></td>
<td width=25%><img src='images/16/connect.png' align=absmiddle> $interfaces[total]</td>
<td width=25% class=green><img src='images/16/if-connect.png' align=absmiddle> $interfaces[up]</td>
<td width=25% class=red><img src='images/16/if-disconnect.png' align=absmiddle> $interfaces[down]</td>
<td width=25% class=grey><img src='images/16/if-disable.png' align=absmiddle> $interfaces[disabled]</td></tr>
</table>");

  echo("<div style='margin: 8px; font-size: 11px; font-weight: bold;'>");

  $sql = "SELECT * FROM interfaces WHERE `device_id` = '" . $device['device_id'] . "' AND deleted != '1'";
  $query = mysql_query($sql);
  while($data = mysql_fetch_array($query)) {
    $data = ifNameDescr($data);
    $data['hostname'] = $device['hostname'];
    echo("$ifsep" . generateiflink($data, makeshortif(strtolower($data['label']))));
    $ifsep = ", ";
  }
  unset($ifsep);
  echo("</div>");

  echo("</div>");

}


?>
